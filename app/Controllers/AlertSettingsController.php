<?php

namespace App\Controllers;

use App\Models\SettingsModel;

class AlertSettingsController extends BaseController
{
    protected $settingsModel;

    public function __construct()
    {
        $this->settingsModel = new SettingsModel();
    }

    // ---------------------------------------------------------------------
    // Mostrar formulario de configuración unificada
    // ---------------------------------------------------------------------
    public function index()
    {
        $emailSettings    = $this->settingsModel->getClassSettings('Email');
        $telegramSettings = $this->settingsModel->getClassSettings('Telegram');
        $slackSettings    = $this->settingsModel->getClassSettings('Slack');

        $data = [
            'title'            => 'Configuración de Alertas',
            'emailSettings'    => $emailSettings,
            'telegramSettings' => $telegramSettings,
            'slackSettings'    => $slackSettings,
        ];

        return view('template/header', $data)
             . view('alerts/settings', $data)
             . view('template/footer');
    }

    // ---------------------------------------------------------------------
    // Guardar todas las configuraciones
    // ---------------------------------------------------------------------
    public function store()
    {
        // Reglas de validación condicionales o generales
        $rules = [
            // Email (solo si se envían datos SMTP)
            'fromEmail' => 'permit_empty|valid_email',
            // Slack
            'slack_webhook_url' => 'permit_empty|valid_url',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // 1. Guardar configuración de Email (SMTP)
        $emailFields = [
            'protocol', 'SMTPHost', 'SMTPUser', 'SMTPPass', 
            'SMTPPort', 'SMTPCrypto', 'mailType', 'fromEmail', 'fromName'
        ];
        foreach ($emailFields as $field) {
            if ($this->request->getPost($field) !== null) {
                $this->settingsModel->setSetting('Email', $field, $this->request->getPost($field));
            }
        }
        $emailEnabled = $this->request->getPost('email_enabled') ? '1' : '0';
        $this->settingsModel->setSetting('Email', 'email_enabled', $emailEnabled);

        // 2. Guardar configuración de Telegram
        $telegramFields = [
            'telegram_bot_token', 'telegram_bot_username', 'telegram_test_chat_id'
        ];
        foreach ($telegramFields as $field) {
            if ($this->request->getPost($field) !== null) {
                $this->settingsModel->setSetting('Telegram', $field, $this->request->getPost($field));
            }
        }
        $telegramEnabled = $this->request->getPost('telegram_enabled') ? '1' : '0';
        $this->settingsModel->setSetting('Telegram', 'telegram_enabled', $telegramEnabled);

        // 3. Guardar configuración de Slack
        $slackFields = [
            'slack_webhook_url'
        ];
        foreach ($slackFields as $field) {
            if ($this->request->getPost($field) !== null) {
                $this->settingsModel->setSetting('Slack', $field, $this->request->getPost($field));
            }
        }
        $slackEnabled = $this->request->getPost('slack_enabled') ? '1' : '0';
        $this->settingsModel->setSetting('Slack', 'slack_enabled', $slackEnabled);

        return redirect()->to('alerts-config')->with('message', 'Configuración de alertas actualizada correctamente.');
    }

    // ---------------------------------------------------------------------
    // Enviar correo de prueba
    // ---------------------------------------------------------------------
    public function testEmail()
    {
        $email = \Config\Services::email();
        $emailSettings = $this->settingsModel->getClassSettings('Email');

        $config = [
            'protocol'    => $this->request->getPost('protocol') ?? ($emailSettings['protocol'] ?? 'smtp'),
            'SMTPHost'    => $this->request->getPost('SMTPHost') ?? ($emailSettings['SMTPHost'] ?? ''),
            'SMTPUser'    => $this->request->getPost('SMTPUser') ?? ($emailSettings['SMTPUser'] ?? ''),
            'SMTPPass'    => $this->request->getPost('SMTPPass') ?? ($emailSettings['SMTPPass'] ?? ''),
            'SMTPPort'    => (int) ($this->request->getPost('SMTPPort') ?? ($emailSettings['SMTPPort'] ?? 587)),
            'SMTPCrypto'  => $this->request->getPost('SMTPCrypto') ?? ($emailSettings['SMTPCrypto'] ?? 'tls'),
            'SMTPTimeout' => 30,
            'mailType'    => $this->request->getPost('mailType') ?? ($emailSettings['mailType'] ?? 'html'),
            'charset'     => 'utf-8',
            'newline'     => "\r\n",
            'CRLF'        => "\r\n"
        ];

        $fromEmail = $this->request->getPost('fromEmail') ?? ($emailSettings['fromEmail'] ?? '');
        $fromName  = $this->request->getPost('fromName') ?? ($emailSettings['fromName'] ?? 'Proxmox Alert');

        if (empty($config['SMTPHost']) || empty($fromEmail)) {
            return redirect()->back()->withInput()->with('active_tab', 'email')->with('error', 'Debe rellenar los datos del servidor para realizar una prueba de correo.');
        }

        $email->initialize($config);
        $email->setFrom($fromEmail, $fromName);
        $email->setTo(auth()->user()->email);
        $email->setSubject('Prueba de Configuración - Proxmox Alert');
        $email->setMessage('<h1>¡Prueba Exitosa!</h1><p>Si has recibido este correo, tu configuración SMTP en Proxmox Alert funciona correctamente.</p><p>Servidor: ' . $config['SMTPHost'] . '</p>');

        if ($email->send()) {
            return redirect()->to('alerts-config')->with('message', 'Correo de prueba enviado correctamente a ' . auth()->user()->email);
        } else {
            return redirect()->back()->withInput()->with('active_tab', 'email')->with('error', 'Error al enviar el correo: ' . $email->printDebugger());
        }
    }

    // ---------------------------------------------------------------------
    // Enviar mensaje de prueba a Telegram
    // ---------------------------------------------------------------------
    public function testTelegram()
    {
        $botToken = $this->request->getPost('telegram_bot_token');
        $chatId   = $this->request->getPost('telegram_test_chat_id');

        if (empty($botToken) || empty($chatId)) {
            return redirect()->back()->withInput()->with('active_tab', 'telegram')->with('error', 'Debe rellenar el Token del Bot y el Chat ID de pruebas para realizar un test de Telegram.');
        }

        $message = "🔔 *Proxmox Alert - Prueba de Configuración*\n\n¡Felicidades! La integración de Telegram con Proxmox Alert se ha configurado correctamente.";
        
        $url = "https://api.telegram.org/bot{$botToken}/sendMessage";
        
        $client = \Config\Services::curlrequest();
        try {
            $response = $client->post($url, [
                'json' => [
                    'chat_id'    => $chatId,
                    'text'       => $message,
                    'parse_mode' => 'Markdown'
                ],
                'headers' => [
                    'Content-Type' => 'application/json'
                ]
            ]);
            
            $resBody = json_decode($response->getBody());
            
            if ($response->getStatusCode() === 200 && isset($resBody->ok) && $resBody->ok) {
                return redirect()->to('alerts-config')->with('message', 'Mensaje de prueba de Telegram enviado correctamente.');
            } else {
                return redirect()->back()->withInput()->with('active_tab', 'telegram')->with('error', 'Error de Telegram: ' . ($resBody->description ?? 'Error desconocido'));
            }
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('active_tab', 'telegram')->with('error', 'Error al conectar con la API de Telegram: ' . $e->getMessage());
        }
    }

    // ---------------------------------------------------------------------
    // Enviar mensaje de prueba a Slack
    // ---------------------------------------------------------------------
    public function testSlack()
    {
        $webhookUrl = $this->request->getPost('slack_webhook_url');

        if (empty($webhookUrl)) {
            return redirect()->back()->withInput()->with('active_tab', 'slack')->with('error', 'Debe rellenar la URL del Webhook de Slack para realizar un test de Slack.');
        }

        $payload = [
            'text' => "🔔 *Proxmox Alert - Prueba de Configuración*\n\n¡Felicidades! La integración de Slack con Proxmox Alert se ha configurado correctamente."
        ];
        
        $client = \Config\Services::curlrequest();
        try {
            $response = $client->post($webhookUrl, [
                'json' => $payload,
                'headers' => [
                    'Content-Type' => 'application/json'
                ]
            ]);
            
            if ($response->getStatusCode() === 200 || trim($response->getBody()) === 'ok') {
                return redirect()->to('alerts-config')->with('message', 'Mensaje de prueba de Slack enviado correctamente.');
            } else {
                return redirect()->back()->withInput()->with('active_tab', 'slack')->with('error', 'Error de Slack: ' . $response->getBody());
            }
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('active_tab', 'slack')->with('error', 'Error al conectar con el Webhook de Slack: ' . $e->getMessage());
        }
    }
}
