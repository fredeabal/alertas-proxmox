<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'HomeController::index');
$routes->get('dashboard/status', 'HomeController::status');

// Rutas de Autenticación Personalizadas
$routes->get('login', '\App\Controllers\Auth\LoginController::loginView');

// Usuarios Management
$routes->group('users', ['namespace' => 'App\Controllers', 'filter' => 'session'], function($routes) {
    $routes->get('/', 'UserController::index', ['filter' => 'permission:users.view']);
    $routes->get('perfil', 'UserController::profile');
    $routes->post('perfil/update', 'UserController::updateProfile');
    $routes->get('create', 'UserController::create', ['filter' => 'permission:users.create']);
    $routes->post('store', 'UserController::store', ['filter' => 'permission:users.create']);
    $routes->get('edit/(:num)', 'UserController::edit/$1', ['filter' => 'permission:users.edit']);
    $routes->post('update/(:num)', 'UserController::update/$1', ['filter' => 'permission:users.edit']);
    $routes->post('delete/(:num)', 'UserController::delete/$1', ['filter' => 'permission:users.delete']);
});

// Empresas Management
$routes->group('companies', ['namespace' => 'App\Controllers', 'filter' => 'session'], function($routes) {
    $routes->get('/', 'CompanyController::index', ['filter' => 'permission:empresas.view']);
    $routes->get('create', 'CompanyController::create', ['filter' => 'permission:empresas.create']);
    $routes->post('store', 'CompanyController::store', ['filter' => 'permission:empresas.create']);
    $routes->get('edit/(:num)', 'CompanyController::edit/$1', ['filter' => 'permission:empresas.edit']);
    $routes->post('update/(:num)', 'CompanyController::update/$1', ['filter' => 'permission:empresas.edit']);
    $routes->get('ping', 'CompanyController::ping', ['filter' => 'permission:empresas.edit']);
    $routes->get('view/(:num)', 'CompanyController::view/$1', ['filter' => 'permission:empresas.view']);
    $routes->get('download-script/(:num)', 'CompanyController::downloadScript/$1', ['filter' => 'permission:empresas.view']);
    $routes->get('get-script/(:num)', 'CompanyController::getScript/$1', ['filter' => 'permission:empresas.view']);
    $routes->post('delete/(:num)', 'CompanyController::delete/$1', ['filter' => 'permission:empresas.delete']);
});

// Alertas Management
$routes->group('alerts', ['namespace' => 'App\Controllers', 'filter' => 'session'], function($routes) {
    $routes->post('resolve/(:num)', 'AlertController::resolve/$1', ['filter' => 'permission:empresas.edit']);
    $routes->post('delete/(:num)', 'AlertController::delete/$1', ['filter' => 'permission:empresas.edit']);
    $routes->post('bulk-action', 'AlertController::bulkAction', ['filter' => 'permission:empresas.edit']);
});

// Configuración de Email
$routes->group('email', ['namespace' => 'App\Controllers', 'filter' => 'session'], function($routes) {
    $routes->get('/', 'EmailController::index', ['filter' => 'group:admin,superadmin']);
    $routes->post('store', 'EmailController::store', ['filter' => 'group:admin,superadmin']);
    $routes->match(['GET', 'POST'], 'test', 'EmailController::test', ['filter' => 'group:admin,superadmin']);
});

// Configuración de IA
$routes->group('ai', ['namespace' => 'App\Controllers', 'filter' => 'session'], function($routes) {
    $routes->get('/', 'AIController::index', ['filter' => 'group:admin,superadmin']);
    $routes->post('store', 'AIController::store', ['filter' => 'group:admin,superadmin']);
    $routes->post('test', 'AIController::test', ['filter' => 'group:admin,superadmin']);
});

// Webhooks (Públicos o con validación de token propia)
$routes->post('webhook/proxmox/(:any)', 'WebhookController::proxmox/$1');

// Endpoint interno para cron (token en .env)
$routes->get('monitoring/ping-check/(:segment)', 'MonitoringController::pingCheck/$1');

service('auth')->routes($routes);
