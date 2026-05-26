<?php

namespace App\Controllers;

use App\Models\CompanyModel;
use App\Models\AlertModel;

class CompanyController extends BaseController
{
    protected $companyModel;

    // ---------------------------------------------------------------------
    // Inicializar modelo de empresas
    // ---------------------------------------------------------------------
    public function __construct()
    {
        $this->companyModel = new CompanyModel();
    }

    // ---------------------------------------------------------------------
    // Listado general de empresas
    // ---------------------------------------------------------------------
    public function index()
    {
        $data = [
            'title'    => 'Gestión de Empresas',
            'empresas' => $this->companyModel->findAll()
        ];

        return view('template/header', $data)
             . view('companies/index')
             . view('template/footer');
    }

    // ---------------------------------------------------------------------
    // Formulario para crear una empresa
    // ---------------------------------------------------------------------
    public function create()
    {
        $data = [
            'title' => 'Nueva Empresa'
        ];

        return view('template/header', $data)
             . view('companies/create')
             . view('template/footer');
    }

    // ---------------------------------------------------------------------
    // Guardar una nueva empresa
    // ---------------------------------------------------------------------
    public function store()
    {
        $rules = [
            'nombre' => 'required|min_length[3]',
            'email'  => 'permit_empty|valid_email',
            'proxmox_host' => 'permit_empty|max_length[255]',
            'logo'   => 'permit_empty|is_image[logo]|max_size[logo,2048]',
        ];

        $messages = [
            'nombre' => [
                'required'   => 'El Nombre de la empresa es obligatorio.',
                'min_length' => 'El Nombre debe tener al menos 3 caracteres.'
            ],
            'email' => [
                'valid_email' => 'Introduce un correo electrónico válido.'
            ],
            'proxmox_host' => [
                'max_length' => 'El IP/Hostname de Proxmox no puede superar los 255 caracteres.'
            ],
            'logo' => [
                'is_image' => 'El archivo debe ser una imagen válida.',
                'max_size' => 'El logo no puede superar los 2MB.'
            ]
        ];

        if (! $this->validate($rules, $messages)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'nombre'        => $this->request->getPost('nombre'),
            'cif'           => $this->request->getPost('cif'),
            'email'         => $this->request->getPost('email'),
            'telefono'      => $this->request->getPost('telefono'),
            'direccion'     => $this->request->getPost('direccion'),
            'proxmox_host'  => $this->request->getPost('proxmox_host'),
            'active'        => $this->request->getPost('active') ? 1 : 0,
            'ai_enabled'    => $this->request->getPost('ai_enabled') ? 1 : 0,
            'webhook_token' => bin2hex(random_bytes(16)), // Token único y seguro
        ];

        // Manejar Logo
        $file = $this->request->getFile('logo');
        if ($file && $file->isValid() && ! $file->hasMoved()) {
            $newName = $file->getRandomName();
            $file->move(FCPATH . 'uploads/logos', $newName);
            $data['logo'] = $newName;
        }

        try {
            $this->companyModel->save($data);
            return redirect()->to('companies')->with('message', 'Empresa creada correctamente.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Error al guardar la empresa: ' . $e->getMessage());
        }
    }

    // ---------------------------------------------------------------------
    // Formulario de edición de empresa
    // ---------------------------------------------------------------------
    public function edit($id)
    {
        $empresa = $this->companyModel->find($id);

        if (! $empresa) {
            return redirect()->to('companies')->with('error', 'Empresa no encontrada.');
        }

        $data = [
            'title'   => 'Editar Empresa',
            'empresa' => $empresa
        ];

        return view('template/header', $data)
             . view('companies/edit')
             . view('template/footer');
    }

    // ---------------------------------------------------------------------
    // Ver detalle de empresa y listado de alertas
    // ---------------------------------------------------------------------
    public function view($id)
    {
        $empresa = $this->companyModel->find($id);

        if (! $empresa) {
            return redirect()->to('/')->with('error', 'Empresa no encontrada.');
        }

        $alertModel = new AlertModel();
        
        // Obtener filtro de severidad si existe
        $severityFilter = $this->request->getGet('severity');
        
        $query = $alertModel->where('empresa_id', $id);
        
        if (!empty($severityFilter)) {
            // Mapeo simple para los filtros
            if ($severityFilter === 'error') {
                $query->whereIn('severity', ['error', 'critical', 'panic', 'emergency']);
            } elseif ($severityFilter === 'warning') {
                $query->where('severity', 'warning');
            } elseif ($severityFilter === 'info') {
                $query->whereIn('severity', ['info', 'notice', 'debug']);
            } elseif ($severityFilter === 'resolved') {
                $query->where('status', 'resolved');
            }
        }

        // Obtener historial de pings para disponibilidad y latencia (últimos 360 registros - 6h a 1 min)
        $pingLogModel = new \App\Models\PingLogModel();
        $pingLogs = $pingLogModel->where('empresa_id', $id)
                                 ->orderBy('created_at', 'DESC')
                                 ->limit(360)
                                 ->findAll();
        
        // Invertir para mostrar en orden cronológico (ASC) en el gráfico
        $pingLogs = array_reverse($pingLogs);

        // Calcular porcentaje de Uptime
        $totalChecks = count($pingLogs);
        $onlineChecks = count(array_filter($pingLogs, function($log) { return $log->status === 'online'; }));
        $uptimePercentage = $totalChecks > 0 ? round(($onlineChecks / $totalChecks) * 100, 2) : null;

        // Calcular latencia promedio
        $latencies = array_filter(array_column($pingLogs, 'latency'));
        $averageLatency = count($latencies) > 0 ? round(array_sum($latencies) / count($latencies), 2) : null;

        $data = [
            'title'            => 'Alertas de ' . $empresa->nombre,
            'empresa'          => $empresa,
            'alertas'          => $query->orderBy('created_at', 'DESC')->paginate(50),
            'pager'            => $alertModel->pager,
            'current_severity' => $severityFilter,
            'pingLogs'         => $pingLogs,
            'uptimePercentage' => $uptimePercentage,
            'averageLatency'   => $averageLatency
        ];

        return view('template/header', $data)
             . view('companies/view', $data)
             . view('template/footer');
    }

    // ---------------------------------------------------------------------
    // Actualizar una empresa existente
    // ---------------------------------------------------------------------
    public function update($id)
    {
        $empresa = $this->companyModel->find($id);

        if (! $empresa) {
            return redirect()->to('companies')->with('error', 'Empresa no encontrada.');
        }

        $rules = [
            'nombre' => 'required|min_length[3]',
            'email'  => 'permit_empty|valid_email',
            'proxmox_host' => 'permit_empty|max_length[255]',
            'logo'   => 'permit_empty|is_image[logo]|max_size[logo,2048]',
        ];

        $messages = [
            'nombre' => [
                'required'   => 'El Nombre de la empresa es obligatorio.',
                'min_length' => 'El Nombre debe tener al menos 3 caracteres.'
            ],
            'email' => [
                'valid_email' => 'Introduce un correo electrónico válido.'
            ],
            'proxmox_host' => [
                'max_length' => 'El IP/Hostname de Proxmox no puede superar los 255 caracteres.'
            ],
            'logo' => [
                'is_image' => 'El archivo debe ser una imagen válida.',
                'max_size' => 'El logo no puede superar los 2MB.'
            ]
        ];

        if (! $this->validate($rules, $messages)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'id'        => $id,
            'nombre'    => $this->request->getPost('nombre'),
            'cif'       => $this->request->getPost('cif'),
            'email'     => $this->request->getPost('email'),
            'telefono'  => $this->request->getPost('telefono'),
            'direccion' => $this->request->getPost('direccion'),
            'proxmox_host' => $this->request->getPost('proxmox_host'),
            'active'    => $this->request->getPost('active') ? 1 : 0,
            'ai_enabled' => $this->request->getPost('ai_enabled') ? 1 : 0,
        ];

        // Asegurar que tenga webhook_token
        if (empty($empresa->webhook_token)) {
            $data['webhook_token'] = bin2hex(random_bytes(16));
        }

        // Manejar Logo
        $file = $this->request->getFile('logo');
        if ($file && $file->isValid() && ! $file->hasMoved()) {
            // Borrar logo anterior si existe
            if (! empty($empresa->logo)) {
                $oldPath = FCPATH . 'uploads/logos/' . $empresa->logo;
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }

            $newName = $file->getRandomName();
            $file->move(FCPATH . 'uploads/logos', $newName);
            $data['logo'] = $newName;
        }

        try {
            $this->companyModel->update($id, $data);
            return redirect()->to('companies')->with('message', 'Empresa actualizada correctamente.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Error al actualizar la empresa: ' . $e->getMessage());
        }
    }

    // ---------------------------------------------------------------------
    // Eliminar empresa (soft delete)
    // ---------------------------------------------------------------------
    public function delete($id)
    {
        if ($this->companyModel->delete($id)) {
            return redirect()->to('companies')->with('message', 'Empresa eliminada correctamente.');
        }

        return redirect()->to('companies')->with('error', 'No se pudo eliminar la empresa.');
    }

    // ---------------------------------------------------------------------
    // Verificar conectividad (ping) hacia IP/hostname de Proxmox
    // ---------------------------------------------------------------------
    public function ping()
    {
        // Host objetivo enviado desde el formulario
        $host = trim((string) $this->request->getGet('host'));

        // Validaciones básicas de entrada
        if ($host === '') {
            return $this->response->setStatusCode(400)->setJSON([
                'ok' => false,
                'message' => 'Debes indicar una IP o hostname.',
            ]);
        }

        if (mb_strlen($host) > 255) {
            return $this->response->setStatusCode(400)->setJSON([
                'ok' => false,
                'message' => 'La IP/hostname es demasiado larga.',
            ]);
        }

        // Aceptar únicamente IP válida o hostname simple
        $isIp = filter_var($host, FILTER_VALIDATE_IP) !== false;
        $isHostname = preg_match('/^[a-zA-Z0-9.-]+$/', $host) === 1;
        if (! $isIp && ! $isHostname) {
            return $this->response->setStatusCode(400)->setJSON([
                'ok' => false,
                'message' => 'Formato inválido. Usa una IP o hostname válido.',
            ]);
        }

        // Construir comando según sistema operativo
        $escapedHost = escapeshellarg($host);
        $command = strtoupper(PHP_OS_FAMILY) === 'DARWIN'
            ? "ping -c 1 -W 2000 {$escapedHost} 2>&1"
            : "ping -c 1 -W 2 {$escapedHost} 2>&1";

        // Ejecutar ping y capturar salida
        $output = [];
        $exitCode = 1;
        exec($command, $output, $exitCode);

        $resultText = implode("\n", $output);

        // Responder en JSON para consumo desde fetch
        if ($exitCode === 0) {
            return $this->response->setJSON([
                'ok' => true,
                'message' => 'Ping OK',
                'details' => $resultText,
            ]);
        }

        return $this->response->setStatusCode(422)->setJSON([
            'ok' => false,
            'message' => 'No responde',
            'details' => $resultText,
        ]);
    }

    // ---------------------------------------------------------------------
    // Generar y descargar el script de configuración para Proxmox
    // ---------------------------------------------------------------------
    public function downloadScript($id)
    {
        $empresa = $this->companyModel->find($id);

        if (! $empresa) {
            return redirect()->back()->with('error', 'Empresa no encontrada.');
        }

        $cleanName = $this->slugify($empresa->nombre);
        $script = $this->generateProxmoxScript($empresa, $cleanName);

        return $this->response->download('setup_proxmox_' . $cleanName . '.sh', $script);
    }

    // ---------------------------------------------------------------------
    // Obtener script de configuración de Proxmox en texto plano
    // ---------------------------------------------------------------------
    public function getScript($id)
    {
        $empresa = $this->companyModel->find($id);

        if (! $empresa) {
            return $this->response->setJSON(['error' => 'Empresa no encontrada']);
        }

        $cleanName = $this->slugify($empresa->nombre);
        $script = $this->generateProxmoxScript($empresa, $cleanName);

        return $this->response->setBody($script)->setHeader('Content-Type', 'text/plain');
    }

    // ---------------------------------------------------------------------
    // Construir script bash para registrar webhook/matcher en Proxmox
    // ---------------------------------------------------------------------
    private function generateProxmoxScript($empresa, $cleanName)
    {
        $webhookName = "webhook-" . $cleanName;
        $matcherName = "matcher-" . $cleanName;
        $webhookUrl = base_url('webhook/proxmox/' . $empresa->webhook_token);

        $script = "cat > /root/setup-webhook.sh << 'EOF'\n";
        $script .= "#!/bin/bash\n";
        $script .= "WEBHOOK_NAME=\"$webhookName\"\n";
        $script .= "WEBHOOK_URL=\"$webhookUrl\"\n";
        $script .= "MATCHER_NAME=\"$matcherName\"\n\n";
        
        $script .= "BODY_B64=$(echo -n '{\"title\":\"{{ escape title }}\",\"message\":\"{{ escape message }}\",\"severity\":\"{{ severity }}\",\"timestamp\":\"{{ timestamp }}\",\"fields\":{{ json fields }}}' | base64 -w 0)\n";
        $script .= "HEADER_VAL_B64=$(echo -n \"application/json\" | base64 -w 0)\n\n";
        
        $script .= "echo \"[1/2] Creando endpoint webhook '\$WEBHOOK_NAME'...\"\n";
        $script .= "pvesh create /cluster/notifications/endpoints/webhook \\\n";
        $script .= "  --name \"\$WEBHOOK_NAME\" \\\n";
        $script .= "  --url \"\$WEBHOOK_URL\" \\\n";
        $script .= "  --method post \\\n";
        $script .= "  --header \"name=Content-Type,value=\$HEADER_VAL_B64\" \\\n";
        $script .= "  --body \"\$BODY_B64\" \\\n";
        $script .= "  --comment \"Webhook hacia Proxmox Alert - " . $empresa->nombre . "\"\n\n";
        
        $script .= "if [ \$? -ne 0 ]; then\n";
        $script .= "  echo \"ERROR: no se pudo crear el endpoint.\"\n";
        $script .= "  exit 1\n";
        $script .= "fi\n\n";
        
        $script .= "echo \"[2/2] Creando matcher '\$MATCHER_NAME'...\"\n";
        $script .= "pvesh create /cluster/notifications/matchers \\\n";
        $script .= "  --name \"\$MATCHER_NAME\" \\\n";
        $script .= "  --target \"\$WEBHOOK_NAME\" \\\n";
        $script .= "  --comment \"Envía todas las alertas de " . $empresa->nombre . "\"\n\n";
        
        $script .= "if [ \$? -ne 0 ]; then\n";
        $script .= "  echo \"ERROR: no se pudo crear el matcher.\"\n";
        $script .= "  exit 1\n";
        $script .= "fi\n\n";
        
        $script .= "echo \"✓ Hecho.\"\n";
        $script .= "EOF\n\n";
        $script .= "bash /root/setup-webhook.sh\n";

        return $script;
    }

    // ---------------------------------------------------------------------
    // Generar slug seguro para nombres de webhook/matcher/archivo
    // ---------------------------------------------------------------------
    private function slugify($text)
    {
        $text = preg_replace('~[^\pL\d]+~u', '_', $text);
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '_');
        $text = preg_replace('~_+~', '_', $text);
        $text = strtolower($text);
        if (empty($text)) return 'empresa';
        return $text;
    }
}
