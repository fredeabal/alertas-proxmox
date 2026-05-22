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
            
            // Canalizar alertas a través del servicio de notificaciones global (SMTP, Telegram, Slack)
            $notificationService = new \App\Libraries\NotificationService();
            $notificationService->sendAll($empresa, $alertaData);

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
}
