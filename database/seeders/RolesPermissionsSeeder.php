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
        'bom.view', 'bom.create', 'bom.approve',
        'mrp.view', 'mrp.run', 'rfq.view', 'rfq.create',
        'invoice.view', 'invoice.verify', 'wms.view', 'qms.view',
        'recall.view', 'recall.create', 'recall.approve',
        'tms.view', 'portal.view', 'webhook.view', 'approval.view', 'catalog.view',
        'document.view', 'document.manage', 'automation.view', 'automation.manage', 'import.view',
    ];

    public const ROLE_PERMS = [
        'super-admin' => '*',
        'admin' => '*',
        'procurement' => ['demand.view', 'pr.view', 'pr.create', 'supplier.view', 'po.view', 'po.create', 'gr.view', 'product.view', 'report.view', 'rfq.view', 'rfq.create', 'invoice.view', 'mrp.view', 'approval.view', 'document.view', 'import.view'],
        'warehouse' => ['gr.view', 'gr.create', 'inventory.view', 'inventory.adjust', 'opname.view', 'opname.create', 'product.view', 'report.view', 'wms.view', 'bom.view', 'document.view', 'import.view'],
        'kitchen' => ['production.view', 'production.create', 'qc.view', 'qc.create', 'qms.view', 'demand.view', 'menu.view', 'product.view', 'inventory.view', 'bom.view', 'document.view'],
        'driver' => ['delivery.view', 'delivery.update', 'distribution.view', 'tms.view'],
        'school' => ['portal.view', 'delivery.view', 'document.view'],
        'finance' => ['invoice.view', 'invoice.verify', 'costing.view', 'report.view', 'approval.view', 'document.view'],
        'qc_manager' => ['qc.view', 'qc.create', 'qms.view', 'production.view', 'inventory.view', 'approval.view', 'document.view', 'report.view'],
        'planning' => ['demand.view', 'mrp.view', 'mrp.run', 'bom.view', 'bom.create', 'menu.view', 'product.view', 'report.view', 'document.view'],
        'auditor' => ['audit.view', 'approval.view', 'report.view', 'document.view', 'inventory.view', 'production.view', 'delivery.view'],
        'viewer' => ['demand.view', 'pr.view', 'po.view', 'gr.view', 'inventory.view', 'production.view', 'distribution.view', 'delivery.view', 'report.view', 'costing.view', 'bom.view', 'mrp.view', 'rfq.view', 'invoice.view', 'wms.view', 'qms.view', 'recall.view', 'tms.view', 'document.view'],
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
