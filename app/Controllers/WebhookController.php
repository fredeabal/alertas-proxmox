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

        // Si la petición no es POST, devolver una respuesta informativa de diagnóstico
        if (strtolower($this->request->getMethod()) !== 'post') {
            return $this->respond([
                'status'  => 'success',
                'message' => 'El endpoint del Webhook está activo y configurado correctamente para la empresa "' . $empresa->nombre . '". Para registrar alertas reales, debes enviar una petición POST con los datos del evento.'
            ], 200);
        }

        // Obtener el cuerpo de la petición (JSON)
        $json = $this->request->getJSON();
        
        if (!$json) {
            // Si no es JSON puro, intentar obtener de los parámetros POST por si acaso
            $json = (object) $this->request->getPost();
        }

        if (empty((array) $json)) {
            return $this->fail('No se recibieron datos válidos.');
        }

        // Manejar de forma robusta si es un objeto anidado (body) o el cuerpo directo
        $body = $json;
        if (is_object($json) && isset($json->body)) {
            $body = $json->body;
        } elseif (is_array($json) && isset($json['body'])) {
            $body = $json['body'];
        }

        // Extraer valores de forma segura previniendo errores de tipo o nulos
        $title = 'Alerta Proxmox';
        $message = '';
        $severity = 'info';
        $resolvedHostname = '';
        $timestamp = '';

        if (is_object($body)) {
            $title = $body->title ?? 'Alerta Proxmox';
            $message = $body->message ?? '';
            $severity = $body->severity ?? 'info';
            $resolvedHostname = !empty($body->hostname) ? $body->hostname : (!empty($body->node) ? $body->node : '');
            $timestamp = $body->timestamp ?? '';
        } elseif (is_array($body)) {
            $title = $body['title'] ?? 'Alerta Proxmox';
            $message = $body['message'] ?? '';
            $severity = $body['severity'] ?? 'info';
            $resolvedHostname = !empty($body['hostname']) ? $body['hostname'] : (!empty($body['node']) ? $body['node'] : '');
            $timestamp = $body['timestamp'] ?? '';
        }

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
            'message'    => $message,
            'severity'   => $severity,
            'hostname'   => $resolvedHostname,
            'timestamp'  => $timestamp,
            'raw_data'   => json_encode($json),
            'status'     => 'new'
        ];

        if ($alertModel->save($alertaData)) {
            log_message('info', 'Alerta guardada para ' . $empresa->nombre . ': ' . $title);
            
            // Lógica de envío de Email si está activado y es una alerta importante
            $sev = strtolower(trim($alertaData['severity']));
            $isImportant = in_array($sev, ['error', 'critical', 'warning', 'unknown']);
            
            if ($empresa->send_email && $isImportant && !empty($empresa->email)) {
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
                            $alertaData['severity'],
                            30 // Aumentado a 30s para mayor fiabilidad con modelos lentos
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
        
        $loginUrl = base_url('companies/view/' . $empresa->id);
        
        $message = "
            <div style='font-family: sans-serif; max-width: 600px; margin: auto; border: 1px solid #eee; border-radius: 12px; overflow: hidden; background: #ffffff;'>
                <div style='background: #5d87ff; padding: 25px; text-align: center; color: white;'>
                    <h2 style='margin: 0; font-size: 20px;'>Notificación de Evento Crítico</h2>
                </div>
                <div style='padding: 25px;'>
                    <p style='color: #333; font-size: 16px;'>Hola <b>{$empresa->nombre}</b>,</p>
                    <p style='color: #666; font-size: 14px;'>Se ha detectado una incidencia importante en tu infraestructura Proxmox que requiere tu atención.</p>
                    
                    <div style='margin: 25px 0; padding: 20px; border-radius: 8px; border: 1px solid #e5eaef; background: #fcfcfc;'>
                        <table style='width: 100%; border-collapse: collapse;'>
                            <tr><td style='padding: 6px 0; color: #888; font-size: 13px; width: 80px;'>Título:</td><td style='font-weight: bold; color: #222;'>{$alerta['title']}</td></tr>
                            <tr><td style='padding: 6px 0; color: #888; font-size: 13px;'>Nodo:</td><td style='font-weight: bold; color: #222;'>{$alerta['hostname']}</td></tr>
                            <tr><td style='padding: 6px 0; color: #888; font-size: 13px;'>Severidad:</td><td><span style='background: #fa896b; color: white; padding: 2px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; text-transform: uppercase;'>{$alerta['severity']}</span></td></tr>
                        </table>
                    </div>

                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='{$loginUrl}' style='background: #5d87ff; color: white; padding: 14px 28px; text-decoration: none; border-radius: 6px; font-weight: bold; display: inline-block; box-shadow: 0 4px 12px rgba(93, 135, 255, 0.2);'>
                            Ver Detalles y Análisis IA
                        </a>
                    </div>

                    <p style='color: #888; font-size: 13px; text-align: center;'>También puedes acceder manualmente desde tu panel de control.</p>
                </div>
                <div style='background: #f8f9fa; padding: 15px; text-align: center; color: #999; font-size: 11px; border-top: 1px solid #eee;'>
                    Proxmox Alert System &bull; © " . date('Y') . "
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
