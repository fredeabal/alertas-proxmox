<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class InstallController extends Controller
{
    protected $session;

    /**
     * Inicializar controlador
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->session = \Config\Services::session();
    }

    /**
     * Pantalla principal del asistente de instalación
     */
    public function index()
    {
        // Si ya existe el archivo físico de bloqueo (install.lock), la app está 100% instalada
        if (file_exists(WRITEPATH . 'install.lock')) {
            return redirect()->to('login')->with('message', 'La aplicación ya se encuentra instalada.');
        }

        // Comprobaciones exhaustivas de requisitos del sistema
        $requirements = [
            'php_version' => [
                'name'    => 'PHP >= 8.2',
                'current' => PHP_VERSION,
                'status'  => version_compare(PHP_VERSION, '8.2.0', '>='),
                'help'    => 'Se requiere PHP versión 8.2.0 o superior para estabilidad y compatibilidad.'
            ],
            'ext_intl' => [
                'name'    => 'Extensión Intl',
                'current' => extension_loaded('intl') ? 'Habilitada' : 'No instalada',
                'status'  => extension_loaded('intl'),
                'help'    => 'Requerida por CodeIgniter para traducciones y formateadores de datos.'
            ],
            'ext_mbstring' => [
                'name'    => 'Extensión Mbstring',
                'current' => extension_loaded('mbstring') ? 'Habilitada' : 'No instalada',
                'status'  => extension_loaded('mbstring'),
                'help'    => 'Requerida para la codificación y manipulación de texto multi-byte.'
            ],
            'ext_sqlite' => [
                'name'    => 'Extensión SQLite3',
                'current' => extension_loaded('pdo_sqlite') ? 'Habilitada' : 'No instalada',
                'status'  => extension_loaded('pdo_sqlite'),
                'help'    => 'Requerida para que el motor de Base de Datos SQLite funcione.'
            ],
            'ext_curl' => [
                'name'    => 'Extensión cURL',
                'current' => extension_loaded('curl') ? 'Habilitada' : 'No instalada',
                'status'  => extension_loaded('curl'),
                'help'    => 'Requerida para la conexión con las APIs de Proxmox y notificaciones.'
            ],
            'write_root' => [
                'name'    => 'Escritura en Raíz (Crear .env)',
                'current' => is_writable(ROOTPATH) ? 'Escritible' : 'Solo lectura',
                'status'  => is_writable(ROOTPATH),
                'help'    => 'El instalador necesita permisos de escritura en la raíz para generar el archivo de configuración (.env).'
            ],
            'write_writable' => [
                'name'    => 'Escritura en carpeta /writable',
                'current' => is_writable(WRITEPATH) ? 'Escritible' : 'Solo lectura',
                'status'  => is_writable(WRITEPATH),
                'help'    => 'Se requieren permisos en /writable para crear la base de datos sqlite y logs.'
            ]
        ];

        // Validar si el sistema es completamente apto para la instalación
        $systemApt = true;
        foreach ($requirements as $req) {
            if (!$req['status']) {
                $systemApt = false;
                break;
            }
        }

        // Sugerir URL base automática basada en el servidor actual
        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') || 
                    (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $suggestedUrl = "{$protocol}://{$host}/";

        $data = [
            'title'        => 'Asistente de Onboarding - Proxmox Alert',
            'requirements' => $requirements,
            'systemApt'    => $systemApt,
            'suggestedUrl' => $suggestedUrl
        ];

        return view('install/index', $data);
    }

    /**
     * Procesa la instalación y creación de tablas/admin
     */
    public function submit()
    {
        if (file_exists(WRITEPATH . 'install.lock')) {
            return redirect()->to('login')->with('message', 'La aplicación ya se encuentra instalada.');
        }

        // Validaciones de formulario
        $validation = \Config\Services::validation();
        $rules = [
            'app_url'        => 'required|valid_url',
            'admin_name'     => 'required|min_length[3]|alpha_numeric_space',
            'admin_email'    => 'required|valid_email',
            'admin_password' => 'required|min_length[8]',
        ];

        $messages = [
            'app_url' => [
                'required'  => 'La URL Base de la aplicación es obligatoria.',
                'valid_url' => 'Debes introducir una URL válida.'
            ],
            'admin_name' => [
                'required'   => 'El nombre de usuario administrador es obligatorio.',
                'min_length' => 'El nombre de usuario debe tener mínimo 3 caracteres.',
            ],
            'admin_email' => [
                'required'    => 'El email del administrador es obligatorio.',
                'valid_email' => 'Debes introducir un email válido.'
            ],
            'admin_password' => [
                'required'   => 'La contraseña es obligatoria.',
                'min_length' => 'La contraseña debe tener mínimo 8 caracteres.'
            ]
        ];

        if (!$this->validate($rules, $messages)) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $appUrl = trim((string)$this->request->getPost('app_url'));
        $adminName = trim((string)$this->request->getPost('admin_name'));
        $adminEmail = trim((string)$this->request->getPost('admin_email'));
        $adminPassword = (string)$this->request->getPost('admin_password');

        try {
            // 1. Crear el archivo SQLite físico si no existe en la carpeta writable
            $dbPath = WRITEPATH . 'database.db';
            if (!file_exists($dbPath)) {
                if (!touch($dbPath)) {
                    throw new \Exception('No se pudo crear el archivo de base de datos en ' . $dbPath . '. Por favor comprueba los permisos de escritura de tu carpeta writable.');
                }
                chmod($dbPath, 0666);
            }

            // 2. Escribir el archivo .env autogenerado de forma robusta
            $envData = [
                'CI_ENVIRONMENT' => 'production',
                'app.baseURL' => rtrim($appUrl, '/') . '/',
                'app.appTimezone' => 'Europe/Madrid',
                'database.default.database' => $dbPath,
                'database.default.DBDriver' => 'SQLite3',
                'encryption.key' => 'hex2bin:' . bin2hex(random_bytes(32)),
                'cron.pingToken' => bin2hex(random_bytes(32)),
                'APP_INSTALLED' => 'true',
            ];

            $content = "#--------------------------------------------------------------------\n";
            $content .= "# CONFIGURACIÓN DE PROXMOX ALERT (AUTOGENERADO POR EL INSTALADOR)\n";
            $content .= "#--------------------------------------------------------------------\n\n";
            foreach ($envData as $key => $value) {
                $content .= "{$key} = '{$value}'\n";
            }
            
            if (file_put_contents(ROOTPATH . '.env', $content) === false) {
                throw new \Exception('No se pudo escribir el archivo .env en la raíz del proyecto. Comprueba los permisos de escritura en la carpeta raíz.');
            }

            // 3. Cargar la base de datos y correr migraciones
            $migration = \Config\Services::migrations();
            $migration->latest();

            // 4. Crear el usuario administrador primario usando Shield
            $users = auth()->getProvider();
            
            // Si ya existe por reintento fallido, recuperarlo para no duplicar
            $existing = $users->where('username', $adminName)->orWhere('email', $adminEmail)->first();
            if ($existing) {
                $user = $existing;
            } else {
                $user = new \CodeIgniter\Shield\Entities\User([
                    'username' => $adminName,
                    'email'    => $adminEmail,
                    'password' => $adminPassword,
                ]);
                $user->active = 1;
                $users->save($user);
                $user = $users->findById($users->getInsertID());
            }

            // Asignar al grupo superadmin si no está asignado
            if (!$user->inGroup('superadmin')) {
                $user->addGroup('superadmin');
            }

            // 5. Crear el archivo físico de bloqueo (lock file)
            file_put_contents(WRITEPATH . 'install.lock', date('Y-m-d H:i:s') . "\n");

            return redirect()->to('login')->with('message', '¡Onboarding completado con éxito! Inicia sesión con tus nuevas credenciales.');

        } catch (\Exception $e) {
            // Limpieza en caso de fallo crítico para poder reintentar
            if (file_exists(ROOTPATH . '.env')) {
                @unlink(ROOTPATH . '.env');
            }
            if (file_exists(WRITEPATH . 'install.lock')) {
                @unlink(WRITEPATH . 'install.lock');
            }

            log_message('error', '[Installer] Fallo catastrófico en instalación: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('errors', ['catastrophic' => 'Error durante el proceso de instalación: ' . $e->getMessage()]);
        }
    }
}
