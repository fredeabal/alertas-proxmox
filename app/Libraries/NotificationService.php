<?php

namespace App\Libraries;

use App\Models\SettingsModel;

class NotificationService
{
    protected $settingsModel;
    protected $emailSettings;
    protected $telegramSettings;
    protected $slackSettings;

    public function __construct()
    {
        $this->settingsModel    = new SettingsModel();
        $this->emailSettings    = $this->settingsModel->getClassSettings('Email');
        $this->telegramSettings = $this->settingsModel->getClassSettings('Telegram');
        $this->slackSettings    = $this->settingsModel->getClassSettings('Slack');
    }

    /**
     * Enviar la alerta a todos los canales habilitados
     */
    public function sendAll($empresa, $alerta)
    {
        $sev = strtolower(trim($alerta['severity'] ?? 'info'));
        
        // Solo enviar alertas importantes (error, critical, warning, unknown, emergency, alert)
        $importantSeverities = ['error', 'critical', 'warning', 'unknown', 'emergency', 'alert', 'crit', 'emerg'];
        $isImportant = false;
        foreach ($importantSeverities as $important) {
            if (strpos($sev, $important) !== false) {
                $isImportant = true;
                break;
            }
        }

        if (!$isImportant) {
            return; // No enviar alertas de tipo puramente informativo (info, notice, debug)
        }

        // 1. Canal de Correo Electrónico
        if ($empresa->send_email && !empty($empresa->email)) {
            $this->sendEmail($empresa, $alerta);
        }

        // 2. Canal de Telegram (Global)
        $telegramEnabled = $this->telegramSettings['telegram_enabled'] ?? '0';
        if ($telegramEnabled === '1') {
            $this->sendTelegram($empresa, $alerta);
        }

        // 3. Canal de Slack (Global)
        $slackEnabled = $this->slackSettings['slack_enabled'] ?? '0';
        if ($slackEnabled === '1') {
            $this->sendSlack($empresa, $alerta);
        }
    }

    /**
     * Enviar alerta por Email (SMTP)
     */
    public function sendEmail($empresa, $alerta)
    {
        if (empty($this->emailSettings['SMTPHost']) || empty($this->emailSettings['fromEmail'])) {
            log_message('error', '[NotificationService] Configuración SMTP no establecida.');
            return false;
        }

        $email = \Config\Services::email();

        $config = [
            'protocol'    => $this->emailSettings['protocol'] ?? 'smtp',
            'SMTPHost'    => $this->emailSettings['SMTPHost'] ?? '',
            'SMTPUser'    => $this->emailSettings['SMTPUser'] ?? '',
            'SMTPPass'    => $this->emailSettings['SMTPPass'] ?? '',
            'SMTPPort'    => (int) ($this->emailSettings['SMTPPort'] ?? 587),
            'SMTPCrypto'  => $this->emailSettings['SMTPCrypto'] ?? 'tls',
            'SMTPTimeout' => 30,
            'mailType'    => $this->emailSettings['mailType'] ?? 'html',
            'charset'     => 'utf-8',
            'newline'     => "\r\n",
            'CRLF'        => "\r\n"
        ];

        $email->initialize($config);
        $email->setFrom($this->emailSettings['fromEmail'], $this->emailSettings['fromName'] ?? 'Proxmox Alert');
        $email->setTo($empresa->email);
        $email->setSubject('⚠️ Alerta de Proxmox - ' . ($alerta['title'] ?? 'Alerta'));
        
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
                            <tr><td style='padding: 6px 0; color: #888; font-size: 13px; width: 80px;'>Empresa:</td><td style='font-weight: bold; color: #222;'>{$empresa->nombre}</td></tr>
                            <tr><td style='padding: 6px 0; color: #888; font-size: 13px;'>Título:</td><td style='font-weight: bold; color: #222;'>{$alerta['title']}</td></tr>
                            <tr><td style='padding: 6px 0; color: #888; font-size: 13px;'>Nodo:</td><td style='font-weight: bold; color: #222;'>{$alerta['hostname']}</td></tr>
                            <tr><td style='padding: 6px 0; color: #888; font-size: 13px;'>Severidad:</td><td><span style='background: #fa896b; color: white; padding: 2px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; text-transform: uppercase;'>{$alerta['severity']}</span></td></tr>
                            " . (!empty($alerta['message']) ? "<tr><td style='padding: 6px 0; color: #888; font-size: 13px;'>Detalle:</td><td style='color: #444; font-size: 13px;'>{$alerta['message']}</td></tr>" : "") . "
                        </table>
                    </div>

                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='{$loginUrl}' style='background: #5d87ff; color: white; padding: 14px 28px; text-decoration: none; border-radius: 6px; font-weight: bold; display: inline-block; box-shadow: 0 4px 12px rgba(93, 135, 255, 0.2);'>
                            Ver Detalles y Análisis IA
                        </a>
                    </div>
                </div>
                <div style='background: #f8f9fa; padding: 15px; text-align: center; color: #999; font-size: 11px; border-top: 1px solid #eee;'>
                    Proxmox Alert System &bull; © " . date('Y') . "
                </div>
            </div>
        ";

        $email->setMessage($message);
        $email->setAltMessage(strip_tags($message));

        if (!$email->send()) {
            log_message('error', '[NotificationService] Error enviando email a ' . $empresa->email . ': ' . $email->printDebugger(['headers']));
            return false;
        }

        log_message('info', '[NotificationService] Email de alerta enviado a ' . $empresa->email);
        return true;
    }

    /**
     * Enviar alerta por Telegram
     */
    public function sendTelegram($empresa, $alerta)
    {
        $botToken = $this->telegramSettings['telegram_bot_token'] ?? '';
        $chatId   = $this->telegramSettings['telegram_test_chat_id'] ?? ''; // Usamos el chat_id configurado

        if (empty($botToken) || empty($chatId)) {
            log_message('error', '[NotificationService] Token de Telegram o Chat ID no configurados.');
            return false;
        }

        $loginUrl = base_url('companies/view/' . $empresa->id);
        
        $title    = $alerta['title'] ?? 'Alerta Proxmox';
        $node     = $alerta['hostname'] ?? 'N/A';
        $severity = strtoupper($alerta['severity'] ?? 'INFO');
        $message  = $alerta['message'] ?? '';

        $telegramMessage = "⚠️ *Alerta de Proxmox - {$empresa->nombre}*\n\n"
                         . "*Incidencia:* {$title}\n"
                         . "*Nodo:* {$node}\n"
                         . "*Severidad:* {$severity}\n";
        
        if (!empty($message)) {
            // Truncar detalle si es muy largo
            if (strlen($message) > 300) {
                $message = substr($message, 0, 300) . '...';
            }
            $telegramMessage .= "*Detalle:* _" . esc($message) . "_\n";
        }

        $telegramMessage .= "\n🔗 [Ver Detalles y Análisis IA]({$loginUrl})";

        $url = "https://api.telegram.org/bot{$botToken}/sendMessage";
        
        $client = \Config\Services::curlrequest();
        try {
            $response = $client->post($url, [
                'json' => [
                    'chat_id'    => $chatId,
                    'text'       => $telegramMessage,
                    'parse_mode' => 'Markdown'
                ],
                'headers' => [
                    'Content-Type' => 'application/json'
                ]
            ]);

            $resBody = json_decode($response->getBody());
            if ($response->getStatusCode() === 200 && isset($resBody->ok) && $resBody->ok) {
                log_message('info', '[NotificationService] Alerta enviada correctamente a Telegram.');
                return true;
            }

            log_message('error', '[NotificationService] Error al enviar a Telegram: ' . ($resBody->description ?? 'Error desconocido'));
            return false;
        } catch (\Exception $e) {
            log_message('error', '[NotificationService] Error cURL Telegram: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Enviar alerta por Slack
     */
    public function sendSlack($empresa, $alerta)
    {
        $webhookUrl = $this->slackSettings['slack_webhook_url'] ?? '';

        if (empty($webhookUrl)) {
            log_message('error', '[NotificationService] Webhook URL de Slack no configurado.');
            return false;
        }

        $loginUrl = base_url('companies/view/' . $empresa->id);
        
        $title    = $alerta['title'] ?? 'Alerta Proxmox';
        $node     = $alerta['hostname'] ?? 'N/A';
        $severity = strtoupper($alerta['severity'] ?? 'INFO');
        $message  = $alerta['message'] ?? '';

        // Estilo de color según severidad
        $color = '#36a64f'; // green
        $sevLower = strtolower($severity);
        if (strpos($sevLower, 'warn') !== false) {
            $color = '#ffcc00'; // yellow
        } elseif (strpos($sevLower, 'err') !== false || strpos($sevLower, 'crit') !== false || strpos($sevLower, 'emerg') !== false) {
            $color = '#ff0033'; // red
        }

        $payload = [
            'attachments' => [
                [
                    'fallback' => "⚠️ Alerta de Proxmox - {$empresa->nombre}: {$title}",
                    'color'    => $color,
                    'pretext'  => "⚠️ *Alerta de Proxmox - {$empresa->nombre}*",
                    'title'    => $title,
                    'title_link' => $loginUrl,
                    'fields'   => [
                        [
                            'title' => 'Nodo / Host',
                            'value' => $node,
                            'short' => true
                        ],
                        [
                            'title' => 'Severidad',
                            'value' => $severity,
                            'short' => true
                        ]
                    ],
                    'footer' => 'Proxmox Alert System',
                    'ts' => time()
                ]
            ]
        ];

        if (!empty($message)) {
            if (strlen($message) > 300) {
                $message = substr($message, 0, 300) . '...';
            }
            $payload['attachments'][0]['fields'][] = [
                'title' => 'Detalle',
                'value' => $message,
                'short' => false
            ];
        }

        $client = \Config\Services::curlrequest();
        try {
            $response = $client->post($webhookUrl, [
                'json' => $payload,
                'headers' => [
                    'Content-Type' => 'application/json'
                ]
            ]);

            if ($response->getStatusCode() === 200 || trim($response->getBody()) === 'ok') {
                log_message('info', '[NotificationService] Alerta enviada correctamente a Slack.');
                return true;
            }

            log_message('error', '[NotificationService] Error al enviar a Slack: ' . $response->getBody());
            return false;
        } catch (\Exception $e) {
            log_message('error', '[NotificationService] Error cURL Slack: ' . $e->getMessage());
            return false;
        }
    }
}
