<?php

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return [
    'permission' => [
        'models' => [
            'permission' => Permission::class,
            'role' => Role::class,
        ],
        'table_names' => [
            'roles' => 'roles',
            'permissions' => 'permissions',
            'model_has_permissions' => 'model_has_permissions',
            'model_has_roles' => 'model_has_roles',
            'role_has_permissions' => 'role_has_permissions',
        ],
        'column_names' => [
            'role_pivot_key' => 'role_id',
            'permission_pivot_key' => 'permission_id',
            'model_morph_key' => 'model_id',
            'team_foreign_key' => 'team_id',
        ],
        'cache' => [
            'expiration_time' => env('PERMISSION_CACHE_EXPIRATION', 86400),
            'key' => 'spatie.permission.cache',
            'model_key' => 'model',
            'store' => 'default',
        ],
        'display_permission_in_exception' => false,
        'display_role_in_exception' => false,
    ],
];
