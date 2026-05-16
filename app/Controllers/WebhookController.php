<?php

namespace App\Controllers;

use App\Models\CompanyModel;
use App\Models\AlertModel;
use CodeIgniter\API\ResponseTrait;

class WebhookController extends BaseController
{
    use ResponseTrait;

    // ---------------------------------------------------------------------
    // Recibir alertas desde Proxmox
    // ---------------------------------------------------------------------
    public function proxmox($token)
    {
        $companyModel = new CompanyModel();
        
        // Validar empresa por token
        $empresa = $companyModel->where('webhook_token', $token)->first();

        if (!$empresa) {
            return $this->failUnauthorized('Token de Webhook inválido.');
        }

        if (!$empresa->active) {
            return $this->failForbidden('La empresa asociada a este Webhook está inactiva.');
        }

        // Obtener el cuerpo de la petición (JSON)
        $json = $this->request->getJSON();
        
        if (!$json) {
            return $this->fail('No se recibieron datos válidos.');
        }

        // Proxmox suele enviar los datos dentro de 'body' (como en tu ejemplo de n8n)
        // o directamente en la raíz. Manejamos ambos casos.
        $body = $json->body ?? $json;

        $title = $body->title ?? 'Alerta Proxmox';
        $resolvedHostname = !empty($body->hostname) ? $body->hostname : (!empty($body->node) ? $body->node : '');

        // Si Proxmox no envía hostname ni node explícitos, intentar extraerlo del título (ej: "vzdump status (pve.local)")
        if (empty($resolvedHostname) && preg_match('/\((.*?)\)/', $title, $matches)) {
            $resolvedHostname = $matches[1];
        }
        
        if (empty($resolvedHostname)) {
            $resolvedHostname = 'N/A (Prueba o Desconocido)';
        }

        // Limpieza inteligente del título (Solo si contiene dos puntos)
        if (strpos($title, ':') !== false) {
            $parts = explode(':', $title, 2);
            if (!empty(trim($parts[1]))) {
                $title = ucfirst(trim($parts[1]));
            }
        }

        $alertModel = new AlertModel();

        $alertaData = [
            'empresa_id' => $empresa->id,
            'title'      => $title,
            'message'    => $body->message ?? '',
            'severity'   => $body->severity ?? 'info',
            'hostname'   => $resolvedHostname,
            'timestamp'  => $body->timestamp ?? '',
            'raw_data'   => json_encode($json),
            'status'     => 'new'
        ];

        if ($alertModel->save($alertaData)) {
            log_message('info', 'Alerta guardada para ' . $empresa->nombre . ': ' . $title);
            
            // Lógica de envío de Email si está activado y es una alerta importante
            $isError = (stripos($alertaData['severity'], 'error') !== false || 
                        stripos($alertaData['severity'], 'crit') !== false || 
                        stripos($alertaData['severity'], 'emerg') !== false ||
                        stripos($alertaData['severity'], 'alert') !== false);
            
            if ($empresa->send_email && $isError && !empty($empresa->email)) {
                $this->sendAlertEmail($empresa, $alertaData);
            }

            // Resumen IA (si está habilitado para esta empresa)
            if ($empresa->ai_enabled) {
                try {
                    $aiService = new \App\Libraries\AIService();
                    if ($aiService->isConfigured()) {
                        $summary = $aiService->generateSummary(
                            $alertaData['title'],
                            $alertaData['message'],
                            $alertaData['severity']
                        );
                        if ($summary) {
                            $alertModel->update($alertModel->getInsertID(), [
                                'ai_summary' => $summary
                            ]);
                        }
                    }
                } catch (\Exception $e) {
                    log_message('error', '[AIService] Error generando resumen: ' . $e->getMessage());
                }
            }

            return $this->respondCreated([
                'status'  => 'success',
                'message' => 'Alerta procesada y guardada correctamente.'
            ]);
        }

        return $this->fail('Error al guardar la alerta en la base de datos.');
    }

    // ---------------------------------------------------------------------
    // Enviar email de alerta usando la configuración SMTP de /email
    // ---------------------------------------------------------------------
    private function sendAlertEmail($empresa, $alerta)
    {
        $settingsModel = new \App\Models\SettingsModel();
        $emailSettings = $settingsModel->getClassSettings('Email');

        // Verificar que hay configuración SMTP guardada
        if (empty($emailSettings['SMTPHost']) || empty($emailSettings['fromEmail'])) {
            log_message('error', 'No se puede enviar email de alerta: configuración SMTP no establecida en /email');
            return;
        }

        $email = \Config\Services::email();

        // Inicializar con la configuración guardada en la base de datos
        $config = [
            'protocol'    => $emailSettings['protocol'] ?? 'smtp',
            'SMTPHost'    => $emailSettings['SMTPHost'] ?? '',
            'SMTPUser'    => $emailSettings['SMTPUser'] ?? '',
            'SMTPPass'    => $emailSettings['SMTPPass'] ?? '',
            'SMTPPort'    => (int) ($emailSettings['SMTPPort'] ?? 587),
            'SMTPCrypto'  => $emailSettings['SMTPCrypto'] ?? 'tls',
            'SMTPTimeout' => 30,
            'mailType'    => $emailSettings['mailType'] ?? 'html',
            'charset'     => 'utf-8',
            'newline'     => "\r\n",
            'CRLF'        => "\r\n"
        ];

        $email->initialize($config);

        $fromEmail = $emailSettings['fromEmail'];
        $fromName  = $emailSettings['fromName'] ?? 'Proxmox Alert';

        $email->setFrom($fromEmail, $fromName);
        $email->setTo($empresa->email);
        $email->setSubject('⚠️ Alerta de Proxmox - ' . $alerta['title']);
        
        $message = "
            <div style='font-family: sans-serif; max-width: 600px; margin: auto; border: 1px solid #eee; border-radius: 10px; overflow: hidden;'>
                <div style='background: #5d87ff; padding: 20px; text-align: center; color: white;'>
                    <h2 style='margin: 0;'>Nueva Alerta Crítica</h2>
                </div>
                <div style='padding: 20px;'>
                    <p>Hola <b>{$empresa->nombre}</b>,</p>
                    <p>Se ha detectado un evento importante en tu infraestructura Proxmox:</p>
                    <hr style='border: none; border-top: 1px solid #eee; margin: 20px 0;'>
                    <table style='width: 100%; border-collapse: collapse;'>
                        <tr><td style='padding: 5px 0; color: #666;'>Título:</td><td style='font-weight: bold;'>{$alerta['title']}</td></tr>
                        <tr><td style='padding: 5px 0; color: #666;'>Nodo/Host:</td><td style='font-weight: bold;'>{$alerta['hostname']}</td></tr>
                        <tr><td style='padding: 5px 0; color: #666;'>Severidad:</td><td style='color: #5d87ff; font-weight: bold;'>{$alerta['severity']}</td></tr>
                        <tr><td style='padding: 5px 0; color: #666;'>Fecha:</td><td>" . date('d/m/Y H:i:s') . "</td></tr>
                    </table>
                    <div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin-top: 20px;'>
                        <p style='margin: 0; color: #333;'><b>Mensaje del sistema:</b></p>
                        <p style='margin: 10px 0 0 0; font-family: monospace; font-size: 13px;'>{$alerta['message']}</p>
                    </div>
                </div>
                <div style='background: #f8f9fa; padding: 15px; text-align: center; color: #999; font-size: 12px;'>
                    Este es un mensaje automático del sistema de alertas de Proxmox.
                </div>
            </div>
        ";

        $email->setMessage($message);
        $email->setAltMessage(strip_tags($message));

        if (!$email->send()) {
            log_message('error', 'Error enviando email de alerta a ' . $empresa->email . ': ' . $email->printDebugger(['headers']));
        } else {
            log_message('info', 'Email de alerta enviado correctamente a ' . $empresa->email);
        }
    }
}
