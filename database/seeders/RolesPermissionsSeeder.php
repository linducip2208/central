<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesPermissionsSeeder extends Seeder
{
    public const PERMISSIONS = [
        'demand.view', 'pr.view', 'pr.create', 'pr.approve',
        'po.view', 'po.create', 'po.approve', 'gr.view', 'gr.create',
        'inventory.view', 'inventory.adjust', 'opname.view', 'opname.create', 'opname.approve',
        'production.view', 'production.create', 'qc.view', 'qc.create',
        'distribution.view', 'distribution.create', 'delivery.view', 'delivery.update',
        'waste.view', 'waste.create', 'costing.view', 'product.view',
        'menu.view', 'supplier.view', 'school.view', 'org.view', 'org.create',
        'user.view', 'user.create', 'role.view', 'audit.view', 'setting.view', 'report.view',
    ];

    public const ROLE_PERMS = [
        'super-admin' => '*',
        'admin' => '*',
        'procurement' => ['demand.view', 'pr.view', 'pr.create', 'supplier.view', 'po.view', 'po.create', 'gr.view', 'product.view', 'report.view'],
        'warehouse' => ['gr.view', 'gr.create', 'inventory.view', 'inventory.adjust', 'opname.view', 'opname.create', 'product.view', 'report.view'],
        'kitchen' => ['production.view', 'production.create', 'qc.view', 'qc.create', 'demand.view', 'menu.view', 'product.view', 'inventory.view'],
        'driver' => ['delivery.view', 'delivery.update', 'distribution.view'],
        'viewer' => ['demand.view', 'pr.view', 'po.view', 'gr.view', 'inventory.view', 'production.view', 'distribution.view', 'delivery.view', 'report.view', 'costing.view'],
    ];

    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach (self::PERMISSIONS as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        foreach (self::ROLE_PERMS as $role => $perms) {
            $r = Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
            $r->syncPermissions($perms === '*' ? Permission::all() : Permission::whereIn('name', $perms)->get());
        }
    }
}
