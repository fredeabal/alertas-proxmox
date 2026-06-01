<?php

namespace App\Controllers;

use App\Models\SettingsModel;
use App\Libraries\AIService;

class AIController extends BaseController
{
    protected $settingsModel;

    public function __construct()
    {
        $this->settingsModel = new SettingsModel();
    }

    /**
     * Muestra el formulario de configuración de IA
     */
    public function index()
    {
        $aiSettings = $this->settingsModel->getClassSettings('AI');

        $data = [
            'title'    => 'Configuración de IA',
            'settings' => $aiSettings,
        ];

        return view('template/header', $data)
            . view('ai/index', $data)
            . view('template/footer');
    }

    /**
     * Guarda la configuración de IA
     */
    public function store()
    {
        if (!$this->saveSettingsFromRequest()) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        return redirect()->to('ai')->with('message', 'Configuración de IA actualizada correctamente.');
    }

    /**
     * Realiza una prueba de generación de resumen
     */
    public function test()
    {
        if (!$this->saveSettingsFromRequest()) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $aiService = new AIService();
        
        $testTitle = "Verificación de Sistema";
        $testMessage = "Hola, esto es una prueba de conexión para verificar que el resumen de inteligencia artificial funciona correctamente.";
        $testSeverity = "info";

        $summary = $aiService->generateSummary($testTitle, $testMessage, $testSeverity);

        if ($summary) {
            return redirect()->to('ai')->with('message', '¡Prueba exitosa! Resumen generado: ' . $summary);
        } else {
            $error = $aiService->getLastError() ?? 'Error desconocido en el servicio.';
            return redirect()->to('ai')->with('error', 'Error en la prueba: ' . $error);
        }
    }

    /**
     * Lógica compartida de validación y guardado
     */
    private function saveSettingsFromRequest(): bool
    {
        $provider = $this->request->getPost('provider');
        $rules = [
            'provider'    => 'required|in_list[gemini,chatgpt,ollama]',
            'model'       => 'required|max_length[100]',
            'api_key'     => 'permit_empty',
            'ollama_host' => 'permit_empty',
            'ollama_port' => 'permit_empty',
        ];

        $messages = [
            'provider' => [
                'required' => 'Debes seleccionar un proveedor de IA.',
                'in_list'  => 'El proveedor seleccionado no es válido.'
            ],
            'model' => [
                'required'   => 'El nombre del modelo es obligatorio.',
                'max_length' => 'El nombre del modelo es demasiado largo.'
            ]
        ];

        if (!$this->validate($rules, $messages)) {
            return false;
        }

        // Validación manual adicional para API Key si es nueva configuración
        $apiKey = trim((string)$this->request->getPost('api_key'));
        $currentSettings = $this->settingsModel->getClassSettings('AI');
        
        if (in_array($provider, ['gemini', 'chatgpt']) && empty($apiKey) && empty($currentSettings['api_key'])) {
            $this->validator->setError('api_key', 'La API Key es obligatoria para este proveedor.');
            return false;
        }

        $fields = ['provider', 'api_key', 'model', 'ollama_host', 'ollama_port'];

        foreach ($fields as $field) {
            $value = $this->request->getPost($field);
            
            // Si el campo es api_key y viene vacío, no sobreescribimos la que ya existe
            if ($field === 'api_key' && empty($value)) {
                continue;
            }
            
            // Valores por defecto para Ollama
            if ($field === 'ollama_host' && empty($value)) $value = 'http://localhost';
            if ($field === 'ollama_port' && empty($value)) $value = '11434';
            
            $this->settingsModel->setSetting('AI', $field, $value);
        }

        return true;
    }

    /**
     * Obtiene la lista de modelos
     */
    public function getModels()
    {
        $provider = $this->request->getPost('provider');
        $apiKey   = $this->request->getPost('api_key');
        $host     = $this->request->getPost('host') ?? 'http://localhost';
        $port     = $this->request->getPost('port') ?? '11434';

        // Si la API Key viene vacía en el POST (porque el input está vacío), intentamos recuperarla de la DB
        if (empty($apiKey)) {
            $apiKey = $this->settingsModel->getSetting('AI', 'api_key')->value ?? '';
        }
        
        $url = '';
        $headers = ['Accept' => 'application/json'];

        if ($provider === 'ollama') {
            if (strpos($host, 'http') !== 0) $host = 'http://' . $host;
            $url = rtrim($host, '/') . ':' . $port . '/api/tags';
        } elseif ($provider === 'gemini') {
            if (empty($apiKey)) return $this->response->setJSON(['status' => 'error', 'message' => 'Falta la API Key']);
            $url = 'https://generativelanguage.googleapis.com/v1beta/openai/models';
            $headers['Authorization'] = 'Bearer ' . $apiKey;
        } elseif ($provider === 'chatgpt') {
            if (empty($apiKey)) return $this->response->setJSON(['status' => 'error', 'message' => 'Falta la API Key']);
            $url = 'https://api.openai.com/v1/models';
            $headers['Authorization'] = 'Bearer ' . $apiKey;
        }

        $client = \Config\Services::curlrequest();
        try {
            $response = $client->get($url, ['headers' => $headers, 'timeout' => 8, 'http_errors' => false]);
            
            if ($response->getStatusCode() === 200) {
                $data = json_decode($response->getBody(), true);
                $models = [];
                
                if ($provider === 'ollama') {
                    if (!empty($data['models'])) foreach ($data['models'] as $m) $models[] = $m['name'];
                } else {
                    if (!empty($data['data'])) {
                        foreach ($data['data'] as $m) {
                            $modelId = $m['id'];
                            
                            if ($provider === 'gemini') {
                                // Quitar prefijo 'models/'
                                if (strpos($modelId, 'models/') === 0) {
                                    $modelId = substr($modelId, 7);
                                }
                                
                                // Filtrar solo modelos Gemini aptos para generación de texto
                                if (
                                    strpos($modelId, 'gemini') === false ||
                                    strpos($modelId, 'embedding') !== false ||
                                    strpos($modelId, 'image') !== false ||
                                    strpos($modelId, 'audio') !== false ||
                                    strpos($modelId, 'tts') !== false ||
                                    strpos($modelId, 'robotics') !== false ||
                                    strpos($modelId, 'computer-use') !== false ||
                                    strpos($modelId, 'deep-research') !== false
                                ) {
                                    continue;
                                }
                            } elseif ($provider === 'chatgpt') {
                                // Filtrar solo modelos OpenAI aptos para chat usando regex a prueba de futuro (gpt-* u o[número]-*)
                                $isChatModel = preg_match('/^(gpt-|o\d+-)/i', $modelId);
                                
                                if (
                                    !$isChatModel ||
                                    strpos($modelId, 'embedding') !== false ||
                                    strpos($modelId, 'moderation') !== false ||
                                    strpos($modelId, 'whisper') !== false ||
                                    strpos($modelId, 'dall-e') !== false ||
                                    strpos($modelId, 'tts') !== false ||
                                    strpos($modelId, 'realtime') !== false ||
                                    strpos($modelId, 'audio') !== false
                                ) {
                                    continue;
                                }
                            }
                            $models[] = $modelId;
                        }
                    }
                }
                
                sort($models);
                return $this->response->setJSON(['status' => 'success', 'models' => $models]);
            }
            return $this->response->setJSON(['status' => 'error', 'message' => 'Error API: ' . $response->getStatusCode()]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}
