<?php

namespace Tests\Feature;

use App\Models\CentralKitchen;
use App\Models\Costing;
use App\Models\Delivery;
use App\Models\Distribution;
use App\Models\GoodsReceipt;
use App\Models\Ingredient;
use App\Models\Menu;
use App\Models\Organization;
use App\Models\Packaging;
use App\Models\Product;
use App\Models\ProductionOrder;
use App\Models\ProductionPlan;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\Recipe;
use App\Models\School;
use App\Models\StockOpname;
use App\Models\Supplier;
use App\Models\Warehouse;
use Tests\TestCase;

class AuthTest extends TestCase
{
    public function test_login_page_renders(): void
    {
        $this->get('/login')->assertOk()->assertSee('MBG Central Kitchen');
    }

    public function test_login_success_and_dashboard(): void
    {
        ['org' => $org] = $this->baseFixtures();
        $this->makeUser($org, 'admin', 'admin@mbg.id');

        $this->post('/login', ['email' => 'admin@mbg.id', 'password' => 'password123'])
            ->assertRedirect('/dashboard');

        $this->get('/dashboard')->assertOk()->assertSee('Dashboard');
    }

    public function test_login_fails_with_wrong_password(): void
    {
        ['org' => $org] = $this->baseFixtures();
        $this->makeUser($org, 'admin', 'admin@mbg.id');

        $this->post('/login', ['email' => 'admin@mbg.id', 'password' => 'salah'])
            ->assertSessionHasErrors('email');
    }

    public function test_inactive_user_cannot_login(): void
    {
        ['org' => $org] = $this->baseFixtures();
        $u = $this->makeUser($org, 'admin', 'off@mbg.id');
        $u->update(['is_active' => false]);

        $this->post('/login', ['email' => 'off@mbg.id', 'password' => 'password123'])
            ->assertSessionHasErrors('email');
    }

    public function test_viewer_cannot_access_admin_pages(): void
    {
        ['org' => $org] = $this->baseFixtures();
        $viewer = $this->makeUser($org, 'viewer', 'view@mbg.id');
        $viewer->removeRole('admin');

        $this->actingAs($viewer)->get('/users')->assertForbidden();
        $this->actingAs($viewer)->get('/settings')->assertForbidden();
    }

    public function test_viewer_can_access_readonly_pages(): void
    {
        ['org' => $org] = $this->baseFixtures();
        $viewer = $this->makeUser($org, 'viewer', 'view2@mbg.id');

        $this->actingAs($viewer)->get('/dashboard')->assertOk();
        $this->actingAs($viewer)->get('/inventory')->assertOk();
    }

    public function test_all_index_pages_render_for_admin(): void
    {
        ['org' => $org] = $this->baseFixtures();
        $admin = $this->makeUser($org, 'admin', 'pages@mbg.id');
        // Master minimal agar halaman tidak error relasi
        $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\MasterSeeder']);

        $pages = [
            '/dashboard', '/suppliers', '/schools', '/recipients', '/ingredients', '/products',
            '/units', '/menus', '/recipes', '/demands', '/purchase-requests', '/purchase-orders',
            '/goods-receipts', '/inventory', '/inventory/movements', '/inventory/adjust',
            '/inventory/transfer', '/inventory/reserve', '/batches',
            '/stock-opnames', '/production-plans', '/production-orders', '/quality-controls',
            '/packagings', '/distributions', '/deliveries', '/wastes', '/costings',
            '/reports', '/reports/stock', '/reports/production', '/reports/delivery',
            '/reports/financial', '/reports/expiry', '/central-kitchens', '/kitchen-units',
            '/warehouses', '/organizations', '/users', '/roles', '/audit-logs', '/settings',
            '/notifications',
        ];
        foreach ($pages as $page) {
            $resp = $this->actingAs($admin)->get($page);
            $this->assertTrue(
                in_array($resp->getStatusCode(), [200, 302]),
                "Halaman {$page} gagal: ".$resp->getStatusCode()
            );
        }
    }

    public function test_cross_organization_access_is_forbidden(): void
    {
        $this->baseFixtures();
        $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\MasterSeeder']);
        $seedOrg = Organization::where('code', 'MBG-01')->first();
        $otherOrg = $this->makeOrg('ORGX');
        $outsider = $this->makeUser($otherOrg, 'admin', 'outsider@x.id');

        $foreignSupplier = Supplier::where('organization_id', $seedOrg->id)->first();
        $this->assertNotNull($foreignSupplier);
        $this->actingAs($outsider)->get(route('suppliers.show', $foreignSupplier))->assertForbidden();

        $foreignIng = Ingredient::where('organization_id', $seedOrg->id)->first();
        $this->actingAs($outsider)->get(route('ingredients.show', $foreignIng))->assertForbidden();
    }

    public function test_forms_and_detail_pages_render(): void
    {
        $this->baseFixtures();
        $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\MasterSeeder']);
        $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\DemoSeeder']);
        // Admin harus satu organisasi dengan data seed (isolasi org diberlakukan).
        $seedOrg = Organization::where('code', 'MBG-01')->first() ?? Organization::first();
        $admin = $this->makeUser($seedOrg, 'admin', 'detail@mbg.id');
        // Pastikan ada opname untuk halaman show (demo tidak membuatnya).
        $seedWh = Warehouse::whereHas('centralKitchen', fn ($q) => $q->where('organization_id', $seedOrg->id))->first();
        if ($seedWh && ! StockOpname::where('organization_id', $seedOrg->id)->exists()) {
            StockOpname::create([
                'organization_id' => $seedOrg->id, 'central_kitchen_id' => $seedWh->central_kitchen_id,
                'warehouse_id' => $seedWh->id, 'number' => 'OPN-TEST-1',
                'opname_date' => now()->toDateString(), 'status' => 'DRAFT', 'counted_by' => $admin->id,
            ]);
        }

        $static = [
            '/suppliers/create', '/schools/create', '/ingredients/create', '/products/create',
            '/menus/create', '/recipes/create', '/demands/create', '/purchase-requests/create',
            '/purchase-orders/create', '/goods-receipts/create', '/inventory/adjust',
            '/stock-opnames/create', '/production-plans/create', '/quality-controls/create',
            '/packagings/create', '/distributions/create', '/wastes/create',
        ];
        foreach ($static as $page) {
            $resp = $this->actingAs($admin)->get($page);
            $this->assertTrue(in_array($resp->getStatusCode(), [200, 302]), "Form {$page}: ".$resp->getStatusCode());
        }

        $oid = $seedOrg->id;
        $dynamic = [
            ['suppliers', Supplier::where('organization_id', $oid)->first()?->id, 'suppliers.show'],
            ['schools', School::where('organization_id', $oid)->first()?->id, 'schools.show'],
            ['ingredients', Ingredient::where('organization_id', $oid)->first()?->id, 'ingredients.show'],
            ['products', Product::where('organization_id', $oid)->first()?->id, 'products.show'],
            ['menus', Menu::where('organization_id', $oid)->first()?->id, 'menus.show'],
            ['recipes', Recipe::where('organization_id', $oid)->first()?->id, 'recipes.show'],
            ['purchase-requests', PurchaseRequest::where('organization_id', $oid)->first()?->id, 'purchase-requests.show'],
            ['purchase-orders', PurchaseOrder::where('organization_id', $oid)->first()?->id, 'purchase-orders.show'],
            ['goods-receipts', GoodsReceipt::where('organization_id', $oid)->first()?->id, 'goods-receipts.show'],
            ['stock-opnames', StockOpname::where('organization_id', $oid)->first()?->id, 'stock-opnames.show'],
            ['production-plans', ProductionPlan::where('organization_id', $oid)->first()?->id, 'production-plans.show'],
            ['production-orders', ProductionOrder::where('organization_id', $oid)->first()?->id, 'production-orders.show'],
            ['packagings', Packaging::where('organization_id', $oid)->first()?->id, 'packagings.show'],
            ['distributions', Distribution::where('organization_id', $oid)->first()?->id, 'distributions.show'],
            ['deliveries', Delivery::where('organization_id', $oid)->first()?->id, 'deliveries.show'],
            ['costings', Costing::where('organization_id', $oid)->first()?->id, 'costings.show'],
            ['central-kitchens', CentralKitchen::where('organization_id', $oid)->first()?->id, 'central-kitchens.show'],
        ];
        foreach ($dynamic as [$label, $id, $route]) {
            $this->assertNotNull($id, "Seed {$label} kosong");
            $resp = $this->actingAs($admin)->get(route($route, $id));
            $this->assertEquals(200, $resp->getStatusCode(), "Detail {$label} gagal: ".$resp->getStatusCode());
        }
    }
}
