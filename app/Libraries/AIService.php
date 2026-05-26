<?php

namespace App\Libraries;

use App\Models\SettingsModel;

class AIService
{
    protected $settings;
    protected $provider;
    protected $apiKey;
    protected $apiUrl;
    protected $model;
    protected $lastError;

    public function __construct()
    {
        $settingsModel = new SettingsModel();
        $this->settings = $settingsModel->getClassSettings('AI');

        $this->provider = $this->settings['provider'] ?? '';
        $this->apiKey   = $this->settings['api_key'] ?? '';
        $this->model    = $this->settings['model'] ?? '';
        
        // Determinar la URL base según el proveedor
        $this->apiUrl = $this->determineApiUrl();
    }

    /**
     * Determina la URL base de la API compatible con OpenAI
     */
    private function determineApiUrl()
    {
        switch ($this->provider) {
            case 'gemini':
                return 'https://generativelanguage.googleapis.com/v1beta/openai/chat/completions';
            case 'chatgpt':
                return 'https://api.openai.com/v1/chat/completions';
            case 'ollama':
                $host = $this->settings['ollama_host'] ?? 'http://localhost';
                $port = $this->settings['ollama_port'] ?? '11434';
                return rtrim($host, '/') . ':' . $port . '/v1/chat/completions';
            default:
                return '';
        }
    }

    /**
     * Verifica si el servicio está mínimamente configurado
     */
    public function isConfigured(): bool
    {
        if (empty($this->provider) || empty($this->apiUrl)) {
            return false;
        }

        if (($this->provider === 'gemini' || $this->provider === 'chatgpt') && empty($this->apiKey)) {
            return false;
        }

        return true;
    }

    /**
     * Genera un resumen de la alerta en español
     */
    public function generateSummary(string $title, string $message, string $severity, int $timeout = 15): ?string
    {
        if (!$this->isConfigured()) {
            return null;
        }

        $prompt = "IMPORTANTE: RESPONDE ÚNICAMENTE CON EL RESUMEN. NO INCLUAS TU PENSAMIENTO, NI RAZONAMIENTO, NI EXPLICACIONES INTERNAS.\n\n";
        $prompt .= "Eres un asistente de sistemas experto en Proxmox VE. Analiza esta alerta técnica y genera un resumen muy breve y claro en español (máximo 2 frases) que explique qué ha ocurrido. No uses formato markdown, solo texto plano.\n\n";
        $prompt .= "Título: {$title}\n";
        $prompt .= "Severidad: {$severity}\n";
        $prompt .= "Mensaje Técnico:\n{$message}";

        return $this->callOpenAICompatible($prompt, $timeout);
    }

    /**
     * Realiza la llamada a la API usando el formato compatible con OpenAI
     */
    private function callOpenAICompatible(string $prompt, int $timeout = 15): ?string
    {
        $client = \Config\Services::curlrequest();
        $targetUrl = $this->apiUrl;

        $headers = [
            'Content-Type' => 'application/json',
            'Accept'       => 'application/json',
        ];

        // Autenticación estándar para proveedores externos
        if ($this->provider === 'gemini' || $this->provider === 'chatgpt') {
            $headers['Authorization'] = 'Bearer ' . $this->apiKey;
        }

        $body = [
            'model' => $this->model ?: ($this->provider === 'ollama' ? 'llama3' : ($this->provider === 'gemini' ? 'gemini-2.0-flash' : 'gpt-4o-mini')),
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'temperature' => 0.3,
            'max_tokens' => 500
        ];

        try {
            $response = $client->post($targetUrl, [
                'headers' => $headers,
                'json'    => $body,
                'timeout' => 60,
                'http_errors' => false
            ]);

            $status = $response->getStatusCode();
            $responseBody = $response->getBody();
            $data = json_decode($responseBody, true);

            if ($status === 200 && isset($data['choices'][0]['message']['content'])) {
                $content = trim($data['choices'][0]['message']['content']);
                
                // Limpiar etiquetas de pensamiento (thought) si el modelo las incluye
                $content = preg_replace('/<thought>.*?<\/thought>/s', '', $content);
                
                return trim($content);
            }

            // Capturar error específico si existe (formato OpenAI, Google o genérico)
            $errorMsg = 'Error desconocido';
            
            // Si la respuesta es un array (como parece sugerir tu error), tomamos el primer elemento
            $errorSource = (isset($data[0])) ? $data[0] : $data;

            if (isset($errorSource['error']['message'])) {
                $errorMsg = $errorSource['error']['message'];
            } elseif (isset($errorSource['message'])) {
                $errorMsg = $errorSource['message'];
            } elseif (is_string($data)) {
                $errorMsg = $data;
            } else {
                $errorMsg = substr($response->getBody(), 0, 150);
            }

            $this->lastError = "API ({$status}): {$errorMsg}";
            
            log_message('error', "[AIService] Cuerpo completo del error: " . $response->getBody());
            return null;

        } catch (\Exception $e) {
            $this->lastError = "Excepción: " . $e->getMessage();
            log_message('error', "[AIService] " . $this->lastError);
            return null;
        }
    }

    /**
     * Obtiene el último error ocurrido
     */
    public function getLastError(): ?string
    {
        return $this->lastError;
    }
}
