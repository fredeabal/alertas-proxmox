<?php

namespace App\Controllers;

class HomeController extends BaseController
{
    // ---------------------------------------------------------------------
    // Mostrar el Dashboard principal
    // ---------------------------------------------------------------------
    public function index()
    {
        if (! auth()->loggedIn()) {
            return redirect()->to('login');
        }

        $companyModel = new \App\Models\CompanyModel();
        $alertModel  = new \App\Models\AlertModel();

        $empresas = $companyModel->where('active', 1)->findAll();
        
        foreach ($empresas as $empresa) {
            $this->calculateStatus($empresa, $alertModel);
        }

        $data = [
            'title'    => "Escritorio",
            'empresas' => $empresas
        ];

        return view('template/header', $data)
             . view('dashboard', $data)
             . view('template/footer');
    }

    // ---------------------------------------------------------------------
    // Endpoint para actualización por AJAX (Fetch)
    // ---------------------------------------------------------------------
    public function status()
    {
        if (! auth()->loggedIn()) {
            return $this->response->setJSON(['error' => 'Unauthorized'])->setStatusCode(401);
        }

        $companyModel = new \App\Models\CompanyModel();
        $alertModel  = new \App\Models\AlertModel();

        $empresas = $companyModel->where('active', 1)->findAll();
        
        foreach ($empresas as $empresa) {
            $this->calculateStatus($empresa, $alertModel);
        }

        return $this->response->setJSON($empresas);
    }

    // ---------------------------------------------------------------------
    // Lógica compartida para calcular estados y contadores
    // ---------------------------------------------------------------------
    private function calculateStatus(&$empresa, $alertModel)
    {
        $alertasNuevasRaw = $alertModel->where('empresa_id', $empresa->id)
                                     ->where('status', 'new')
                                     ->findAll();
                                     
        // Filtrar 'info' y 'notice' puramente informativas para que no disparen el estado de alerta
        $alertasNuevas = array_filter($alertasNuevasRaw, function($alerta) {
            $sev = strtolower(trim($alerta->severity));
            return !in_array($sev, ['info', 'notice']);
        });
                                     
        $empresa->border_class = ''; 
        $badgeCount = count($alertasNuevas);

        if ($badgeCount > 0) {
            $hasError = false;
            $hasWarning = false;
            
            foreach ($alertasNuevas as $alerta) {
                $sev = strtolower(trim($alerta->severity));
                
                if (in_array($sev, ['error', 'critical', 'unknown'])) {
                    $hasError = true;
                } elseif (in_array($sev, ['warning'])) {
                    $hasWarning = true;
                }
            }
            
            if ($hasError) {
                $empresa->border_class = 'border-danger';
            } elseif ($hasWarning) {
                $empresa->border_class = 'border-warning';
            }
        }
        
        $empresa->alert_count = $badgeCount;
        
        // Color para el LED parpadeante
        $pulseColor = 'success';
        if (strpos($empresa->border_class, 'danger') !== false) {
            $pulseColor = 'danger';
        } elseif (strpos($empresa->border_class, 'warning') !== false) {
            $pulseColor = 'warning';
        } elseif (strpos($empresa->border_class, 'info') !== false) {
            $pulseColor = 'info';
        }
        
        $empresa->pulse_color = $pulseColor;
    }
}
