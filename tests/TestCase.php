<?php

namespace Tests;

use App\Models\CentralKitchen;
use App\Models\Ingredient;
use App\Models\Organization;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use RefreshDatabase;

    protected array $seeded = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
    }

    protected function seedRoles(): void
    {
        foreach (['super-admin', 'admin', 'warehouse', 'kitchen', 'procurement', 'driver', 'school', 'viewer'] as $r) {
            Role::firstOrCreate(['name' => $r, 'guard_name' => 'web']);
        }
        $perms = [
            'demand.view', 'pr.view', 'pr.create', 'pr.approve',
            'po.view', 'po.create', 'po.approve', 'gr.view', 'gr.create',
            'inventory.view', 'inventory.adjust', 'opname.view', 'opname.create', 'opname.approve',
            'production.view', 'production.create', 'qc.view', 'qc.create',
            'distribution.view', 'distribution.create', 'delivery.view', 'delivery.update',
            'waste.view', 'waste.create', 'costing.view', 'product.view',
            'menu.view', 'supplier.view', 'school.view', 'org.view', 'org.create',
            'user.view', 'role.view', 'audit.view', 'setting.view', 'report.view',
            'bom.view', 'bom.create', 'bom.approve',
            'mrp.view', 'mrp.run', 'rfq.view', 'rfq.create',
            'invoice.view', 'invoice.verify', 'wms.view', 'qms.view',
            'recall.view', 'recall.create', 'recall.approve',
            'tms.view', 'portal.view', 'webhook.view', 'approval.view', 'catalog.view',
        ];
        foreach ($perms as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }
        Role::findByName('admin')->syncPermissions(Permission::all());
        Role::findByName('viewer')->syncPermissions([
            'demand.view', 'pr.view', 'po.view', 'gr.view', 'inventory.view',
            'production.view', 'distribution.view', 'delivery.view', 'report.view', 'costing.view',
        ]);
        Role::findByName('school')->syncPermissions(['portal.view', 'delivery.view']);
        Role::findByName('driver')->syncPermissions(['delivery.view', 'delivery.update', 'distribution.view', 'tms.view']);
    }

    protected function makeOrg(string $code = 'ORG1'): Organization
    {
        return Organization::create(['code' => $code, 'name' => "Org $code", 'slug' => strtolower($code), 'status' => 'ACTIVE']);
    }

    protected function makeKitchen(Organization $org, string $code = 'CK1'): CentralKitchen
    {
        return CentralKitchen::create(['organization_id' => $org->id, 'code' => $code, 'name' => "Kitchen $code", 'slug' => strtolower($code), 'daily_capacity' => 1000, 'status' => 'ACTIVE']);
    }

    protected function makeWarehouse(CentralKitchen $ck, string $code = 'WH1'): Warehouse
    {
        return Warehouse::create(['central_kitchen_id' => $ck->id, 'code' => $code, 'name' => "Warehouse $code", 'warehouse_type' => 'DRY', 'status' => 'ACTIVE']);
    }

    protected function makeUnit(string $code = 'KG'): Unit
    {
        return Unit::firstOrCreate(['code' => $code], ['name' => $code, 'symbol' => strtolower($code), 'unit_type' => 'WEIGHT', 'is_base' => true, 'is_active' => true]);
    }

    protected function makeIngredient(Organization $org, Unit $unit, string $code = 'ING1'): Ingredient
    {
        return Ingredient::create(['organization_id' => $org->id, 'code' => $code, 'name' => "Ingredient $code", 'category' => 'STAPLE', 'unit_id' => $unit->id, 'standard_price' => 10000, 'is_active' => true]);
    }

    protected function makeProduct(Organization $org, Unit $unit, string $code = 'PRD1'): Product
    {
        return Product::create(['organization_id' => $org->id, 'code' => $code, 'name' => "Product $code", 'category' => 'MEAL', 'unit_id' => $unit->id, 'is_active' => true]);
    }

    protected function makeUser(Organization $org, string $role = 'admin', string $email = 'admin@test.id'): User
    {
        $u = User::create(['organization_id' => $org->id, 'name' => ucfirst($role), 'email' => $email, 'password' => 'password123', 'is_active' => true]);
        $u->assignRole($role);

        return $u;
    }

    protected function baseFixtures(): array
    {
        if (isset($this->seeded['base'])) {
            return $this->seeded['base'];
        }
        $org = $this->makeOrg();
        $ck = $this->makeKitchen($org);
        $wh = $this->makeWarehouse($ck);
        $unit = $this->makeUnit();
        $ing = $this->makeIngredient($org, $unit);
        $prd = $this->makeProduct($org, $unit);

        return $this->seeded['base'] = compact('org', 'ck', 'wh', 'unit', 'ing', 'prd');
    }
}
