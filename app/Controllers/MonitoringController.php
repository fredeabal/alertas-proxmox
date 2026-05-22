<?php

namespace App\Controllers;

use App\Models\AlertModel;
use App\Models\CompanyModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class MonitoringController extends BaseController
{
    // ---------------------------------------------------------------------
    // Ejecutar chequeo masivo de ping para empresas activas (endpoint cron)
    // ---------------------------------------------------------------------
    public function pingCheck(string $token)
    {
        $expectedToken = (string) env('cron.pingToken');
        if ($expectedToken === '' || ! hash_equals($expectedToken, $token)) {
            throw PageNotFoundException::forPageNotFound();
        }

        $companyModel = new CompanyModel();
        $alertModel = new AlertModel();

        $empresas = $companyModel
            ->where('active', 1)
            ->where('proxmox_host IS NOT NULL')
            ->where('proxmox_host !=', '')
            ->findAll();

        $summary = [
            'total' => count($empresas),
            'ok' => 0,
            'failed' => 0,
            'alerts_created' => 0,
            'alerts_skipped' => 0,
            'alerts_resolved' => 0,
            'checked_at' => date('c'),
        ];

        foreach ($empresas as $empresa) {
            $host = trim((string) ($empresa->proxmox_host ?? ''));
            if ($host === '') {
                continue;
            }

            [$isReachable, $output] = $this->runPing($host);

            // Extraer latencia si el host responde
            $latency = null;
            if ($isReachable) {
                $latency = $this->parseLatency($output);
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
                $summary['ok']++;
                if ($this->resolveOpenPingAlert($alertModel, (int) $empresa->id, $host)) {
                    $summary['alerts_resolved']++;
                }
                continue;
            }

            $summary['failed']++;
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
                    $summary['alerts_created']++;

                    // Canalizar alertas a través del servicio de notificaciones global
                    $notificationService = new \App\Libraries\NotificationService();
                    $notificationService->sendAll($empresa, $alertaData);
                }
            } else {
                $summary['alerts_skipped']++;
            }
        }

        // Limpiar registros antiguos (más de 7 días) para no saturar SQLite
        $pingLogModel = new \App\Models\PingLogModel();
        $sevenDaysAgo = date('Y-m-d H:i:s', strtotime('-7 days'));
        $pingLogModel->where('created_at <', $sevenDaysAgo)->delete();

        return $this->response->setJSON([
            'ok' => true,
            'message' => 'Ping check ejecutado',
            'summary' => $summary,
        ]);
    }

    // ---------------------------------------------------------------------
    // Ejecutar ping a un host y devolver estado/salida
    // ---------------------------------------------------------------------
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

    // ---------------------------------------------------------------------
    // Evitar alertas duplicadas mientras exista una alerta abierta
    // ---------------------------------------------------------------------
    private function shouldCreatePingAlert(AlertModel $alertModel, int $empresaId, string $host): bool
    {
        $existing = $alertModel
            ->where('empresa_id', $empresaId)
            ->whereIn('title', ['Proxmox no responde', 'Ping Proxmox no responde'])
            ->where('status !=', 'resolved')
            ->first();

        return $existing === null;
    }

    // ---------------------------------------------------------------------
    // Resolver alerta abierta de ping cuando el host vuelve a responder
    // ---------------------------------------------------------------------
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



    // ---------------------------------------------------------------------
    // Extraer latencia media del comando ping (soporta Darwin y Linux)
    // ---------------------------------------------------------------------
    private function parseLatency(string $output): ?float
    {
        // 1. Intentar extraer latencia individual de respuesta del paquete (time=XX.XX ms)
        if (preg_match('/time=([0-9.]+)\s*ms/i', $output, $matches)) {
            return (float) $matches[1];
        }

        // 2. Fallback: buscar latencia promedio en las estadísticas RTT (round-trip/rtt min/avg/max/stddev = .../avg/...)
        if (preg_match('/(?:round-trip|rtt)\s+\S+\s+=\s+[0-9.]+\/([0-9.]+)\/[0-9.]+\/[0-9.]+/i', $output, $matches)) {
            return (float) $matches[1];
        }

        return null;
    }
}
