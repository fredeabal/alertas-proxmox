<?php

namespace App\Controllers;

use App\Models\CompanyModel;
use App\Models\AlertModel;
use CodeIgniter\API\ResponseTrait;

class WebhookController extends BaseController
{
    use ResponseTrait;

    // ---------------------------------------------------------------------
    // Recibir alertas desde Proxmox / Apprise
    // ---------------------------------------------------------------------
    public function proxmox($token)
    {
        $companyModel = new CompanyModel();
        $empresa = $companyModel->where('webhook_token', $token)->first();

        if (!$empresa) {
            return $this->failUnauthorized('Token de Webhook inválido.');
        }
        if (!$empresa->active) {
            return $this->failForbidden('Empresa inactiva.');
        }

        // Respuesta rápida a peticiones GET u otras
        if (strtolower($this->request->getMethod()) !== 'post') {
            return $this->respond(['status' => 'success', 'message' => 'Endpoint activo.'], 200);
        }

        // 1. Obtener y asegurar el Payload (Soporte Apprise Ping vacío)
        $rawBody = $this->request->getBody();
        $json = $this->request->getJSON(true) ?? json_decode($rawBody, true) ?? $this->request->getPost();
        
        if (empty($json)) {
            $json = []; 
        }

        // 2. Extraer el Body principal (Apprise suele enviarlo todo dentro de "body" o como texto plano)
        $body = $json;
        if (isset($json['body']) && (is_array($json['body']) || is_object($json['body']))) {
            $body = $json['body'];
        }

        // Unificar todo a array para simplificar lecturas
        $body = json_decode(json_encode($body), true) ?: [];

        // 3. Extracción y Mapeo de Datos
        $title = $body['title'] ?? 'Alerta Proxmox';
        $message = $body['message'] ?? $body['body'] ?? '';
        
        $type = strtolower($body['type'] ?? $body['severity'] ?? 'info');
        $severity = match ($type) {
            'failure' => 'error',
            'success' => 'info',
            default => $type
        };

        $resolvedHostname = $body['hostname'] ?? $body['node'] ?? '';
        if (empty($resolvedHostname) && preg_match('/\((.*?)\)/', $title, $matches)) {
            $resolvedHostname = $matches[1];
        }
        $resolvedHostname = $resolvedHostname ?: 'N/A';

        if (strpos($title, ':') !== false) {
            $parts = explode(':', $title, 2);
            if (!empty(trim($parts[1]))) {
                $title = ucfirst(trim($parts[1]));
            }
        }

        $timestamp = $body['timestamp'] ?? date('Y-m-d H:i:s');

        // 4. Guardar Alerta
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

        if (!$alertModel->save($alertaData)) {
            return $this->fail('Error al guardar la alerta.');
        }

        log_message('info', "Alerta guardada para {$empresa->nombre}: {$title}");

        // 5. Responder INMEDIATAMENTE con 200 OK para evitar Timeouts de Apprise/Proxmox
        $this->response->setStatusCode(200)->setJSON([
            'status'  => 'success',
            'message' => 'Alerta procesada correctamente.'
        ])->send();

        // Liberar el hilo de respuesta (el cliente recibe el 200 OK y se desconecta)
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        } else {
            ignore_user_abort(true);
            if (ob_get_level() > 0) ob_end_flush();
            flush();
        }

        // 6. Ejecutar notificaciones e IA en segundo plano
        (new \App\Libraries\NotificationService())->sendAll($empresa, $alertaData);

        if ($empresa->ai_enabled) {
            try {
                $aiService = new \App\Libraries\AIService();
                if ($aiService->isConfigured()) {
                    $summary = $aiService->generateSummary($title, $message, $severity, 30);
                    if ($summary) {
                        $alertModel->update($alertModel->getInsertID(), ['ai_summary' => $summary]);
                    }
                }
            } catch (\Exception $e) {
                log_message('error', '[AIService] Error generando resumen: ' . $e->getMessage());
            }
        }

        exit;
    }
}
