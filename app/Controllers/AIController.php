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
            'settings' => $aiSettings
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
        $fields = [
            'provider', 'api_key', 'model', 'ollama_host', 'ollama_port'
        ];

        foreach ($fields as $field) {
            $value = $this->request->getPost($field);
            $this->settingsModel->setSetting('AI', $field, $value);
        }

        return redirect()->to('ai')->with('message', 'Configuración de IA actualizada correctamente.');
    }

    /**
     * Realiza una prueba de generación de resumen
     */
    public function test()
    {
        // Guardamos primero para probar con los datos actuales del formulario
        $this->store();

        $aiService = new AIService();
        
        $testTitle = "Prueba de Conexión";
        $testMessage = "El servidor Proxmox ha reportado un uso de CPU superior al 90% en la máquina virtual 101 (Producción).";
        $testSeverity = "warning";

        $summary = $aiService->generateSummary($testTitle, $testMessage, $testSeverity);

        if ($summary) {
            return redirect()->to('ai')->with('message', '¡Prueba exitosa! Resumen generado: ' . $summary);
        } else {
            $error = $aiService->getLastError() ?? 'Error desconocido en el servicio.';
            return redirect()->to('ai')->with('error', 'Error en la prueba: ' . $error);
        }
    }
}
