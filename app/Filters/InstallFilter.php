<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class InstallFilter implements FilterInterface
{
    /**
     * Verifica el estado de instalación antes de procesar la petición
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $router = service('router');
        $controller = $router->controllerName();
        
        // Evitar bucles en las rutas del instalador o recursos estáticos
        if (
            strpos($controller, 'InstallController') !== false ||
            $request->isCLI() ||
            preg_match('/\.(js|css|png|jpg|jpeg|gif|svg|ico|woff|woff2|ttf|eot)$/i', $request->getUri()->getPath())
        ) {
            return;
        }

        // Si ya existe el archivo .env, la app está instalada. 
        // No intervenimos para evitar cualquier bucle de redirección en producción.
        if (file_exists(ROOTPATH . '.env')) {
            return;
        }

        // Redirigir al asistente de instalación
        return redirect()->to('install');
    }

    /**
     * Post-procesador
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No hacer nada
    }
}
