<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter Shield.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Config;

use CodeIgniter\Shield\Config\AuthGroups as ShieldAuthGroups;

class AuthGroups extends ShieldAuthGroups
{
    /**
     * --------------------------------------------------------------------
     * Default Group
     * --------------------------------------------------------------------
     * The group that a newly registered user is added to.
     */
    public string $defaultGroup = 'user';

    /**
     * --------------------------------------------------------------------
     * Groups
     * --------------------------------------------------------------------
     * Maps groups to their title and description.
     */
    public array $groups = [
        'superadmin' => [
            'title'       => 'Super Administrador',
            'description' => 'Acceso total al sistema.',
        ],
        'admin' => [
            'title'       => 'Administrador',
            'description' => 'Gestión de usuarios y empresas.',
        ],
        'user' => [
            'title'       => 'Usuario',
            'description' => 'Acceso básico al dashboard.',
        ],
    ];

    /**
     * --------------------------------------------------------------------
     * Permissions
     * --------------------------------------------------------------------
     * Maps permissions to their title and description.
     */
    public array $permissions = [
        'admin.access'        => 'Acceso al panel administrativo',
        'users.view'          => 'Ver lista de usuarios',
        'users.create'        => 'Crear nuevos usuarios',
        'users.edit'          => 'Editar usuarios',
        'users.delete'        => 'Eliminar usuarios',
        'empresas.view'       => 'Ver empresas',
        'empresas.create'     => 'Crear empresas',
        'empresas.edit'       => 'Editar empresas',
        'empresas.delete'     => 'Eliminar empresas',
        'alertas.view'        => 'Ver canales de alerta',
        'alertas.manage'      => 'Gestionar canales de alerta',
        'ai.view'             => 'Ver configuración de IA',
        'ai.manage'           => 'Gestionar configuración de IA',
    ];

    /**
     * --------------------------------------------------------------------
     * Permissions Matrix
     * --------------------------------------------------------------------
     * Maps permissions to groups.
     */
    public array $matrix = [
        'superadmin' => [
            'admin.*',
            'users.*',
            'empresas.*',
            'alertas.*',
            'ai.*',
        ],
        'admin' => [
            'admin.access',
            'users.view',
            'users.create',
            'users.edit',
            'empresas.view',
            'empresas.edit',
            'alertas.view',
            'alertas.manage',
            'ai.manage',
        ],
        'user' => [
            // Sin permisos por defecto, se asignan granularmente
        ],
    ];
}
