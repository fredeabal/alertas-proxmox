<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\AlertModel;
use App\Models\CompanyModel;

class MonitorPing extends BaseCommand
{
    protected $group       = 'Monitoring';
    protected $name        = 'monitor:ping';
    protected $description = 'Ejecuta el chequeo masivo de ping para empresas activas.';

    public function run(array $params)
    {
        CLI::write("Iniciando monitoreo de ping...", "yellow");

        $companyModel = new CompanyModel();
        $alertModel = new AlertModel();

        $empresas = $companyModel
            ->where('active', 1)
            ->where('proxmox_host IS NOT NULL')
            ->where('proxmox_host !=', '')
            ->findAll();

        $total = count($empresas);
        CLI::write("Empresas activas encontradas: {$total}", "cyan");

        if ($total === 0) {
            CLI::write("No hay empresas activas con host configurado.", "green");
            return;
        }

        foreach ($empresas as $empresa) {
            $host = trim((string) ($empresa->proxmox_host ?? ''));
            if ($host === '') {
                continue;
            }

            CLI::write("Haciendo ping a {$empresa->nombre} ({$host})...", "yellow");

            [$isReachable, $output] = $this->runPing($host);

            $latency = null;
            if ($isReachable) {
                $latency = $this->parseLatency($output);
                CLI::write("  -> ONLINE - Latencia: " . ($latency !== null ? "{$latency} ms" : "desconocida"), "green");
            } else {
                CLI::write("  -> OFFLINE", "red");
            }

            // Registrar el log de disponibilidad y latencia
            $pingLogModel = new \App\Models\PingLogModel();
            $pingLogModel->insert([
                'empresa_id' => $empresa->id,
                'status'     => $isReachable ? 'online' : 'offline',
                'latency'    => $latency,
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            if ($isReachable) {
                if ($this->resolveOpenPingAlert($alertModel, (int) $empresa->id, $host)) {
                    CLI::write("  -> Alerta resuelta y notificada.", "green");
                }
                continue;
            }

            if ($this->shouldCreatePingAlert($alertModel, (int) $empresa->id, $host)) {
                $downAt = date('Y-m-d H:i:s');
                $alertaData = [
                    'empresa_id' => $empresa->id,
                    'title' => 'Proxmox no responde',
                    'message' => "Incidente de conectividad detectado en {$host}. Caída registrada a las {$downAt}.",
                    'severity' => 'error',
                    'hostname' => $host,
                    'timestamp' => date('c'),
                    'raw_data' => json_encode([
                        'source' => 'cron_ping_check',
                        'host' => $host,
                        'down_at' => $downAt,
                        'output' => $output,
                    ], JSON_UNESCAPED_UNICODE),
                    'status' => 'new',
                ];

                if ($alertModel->insert($alertaData)) {
                    CLI::write("  -> Nueva alerta creada en base de datos.", "red");

                    // Canalizar alertas a través del servicio de notificaciones global
                    $notificationService = new \App\Libraries\NotificationService();
                    $notificationService->sendAll($empresa, $alertaData);
                    CLI::write("  -> Notificaciones enviadas.", "cyan");
                }
            }
        }

        // Limpiar registros antiguos (más de 7 días) para no saturar SQLite
        $pingLogModel = new \App\Models\PingLogModel();
        $sevenDaysAgo = date('Y-m-d H:i:s', strtotime('-7 days'));
        $pingLogModel->where('created_at <', $sevenDaysAgo)->delete();
        CLI::write("Limpieza de logs antiguos completada.", "green");

        CLI::write("Chequeo de ping completado correctamente.", "green");
    }

    private function runPing(string $host): array
    {
        $escapedHost = escapeshellarg($host);
        $command = strtoupper(PHP_OS_FAMILY) === 'DARWIN'
            ? "ping -c 1 -W 2000 {$escapedHost} 2>&1"
            : "ping -c 1 -W 2 {$escapedHost} 2>&1";

        $output = [];
        $exitCode = 1;
        exec($command, $output, $exitCode);

        return [$exitCode === 0, implode("\n", $output)];
    }

    private function shouldCreatePingAlert(AlertModel $alertModel, int $empresaId, string $host): bool
    {
        $existing = $alertModel
            ->where('empresa_id', $empresaId)
            ->whereIn('title', ['Proxmox no responde', 'Ping Proxmox no responde'])
            ->where('status !=', 'resolved')
            ->first();

        return $existing === null;
    }

    private function resolveOpenPingAlert(AlertModel $alertModel, int $empresaId, string $host): bool
    {
        $db = \Config\Database::connect();
        $builder = $db->table('alertas');
        $recoveredAt = date('Y-m-d H:i:s');

        $builder->where('empresa_id', $empresaId)
            ->whereIn('title', ['Proxmox no responde', 'Ping Proxmox no responde'])
            ->where('status !=', 'resolved');

        $existingCount = $builder->countAllResults(false);
        if ($existingCount < 1) {
            return false;
        }

        $builder->update([
            'status' => 'resolved',
            'message' => "Conectividad restablecida en {$host} a las {$recoveredAt}.",
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return true;
    }

    private function parseLatency(string $output): ?float
    {
        if (preg_match('/time=([0-9.]+)\s*ms/i', $output, $matches)) {
            return (float) $matches[1];
        }

        if (preg_match('/(?:round-trip|rtt)\s+\S+\s+=\s+[0-9.]+\/([0-9.]+)\/[0-9.]+\/[0-9.]+/i', $output, $matches)) {
            return (float) $matches[1];
        }

        return null;
    }
}
