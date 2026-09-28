<?php

namespace Tests\Feature;

use App\Models\Delivery;
use App\Models\School;
use App\Models\Supplier;
use App\Models\Webhook;
use App\Services\InventoryService;
use App\Services\TotpService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class Enterprise2Test extends TestCase
{
    public function test_fifo_warehouse_consumes_oldest_first(): void
    {
        ['org' => $org, 'wh' => $wh, 'unit' => $unit, 'ing' => $ing] = $this->baseFixtures();
        $wh->update(['fifo_method' => 'FIFO']);
        $svc = app(InventoryService::class);
        // Batch tua expired lama vs batch muda expired cepat: FIFO ambil yang tua.
        $old = $svc->receive(['organization_id' => $org->id, 'warehouse_id' => $wh->id, 'item_type' => 'ingredient', 'item_id' => $ing->id, 'qty' => 10, 'unit_id' => $unit->id, 'unit_cost' => 1000, 'batch_no' => 'FIFO-OLD', 'expiry_date' => now()->addDays(90)->toDateString(), 'reference_type' => 'T', 'reference_id' => 141]);
        sleep(1);
        $svc->receive(['organization_id' => $org->id, 'warehouse_id' => $wh->id, 'item_type' => 'ingredient', 'item_id' => $ing->id, 'qty' => 10, 'unit_id' => $unit->id, 'unit_cost' => 1000, 'batch_no' => 'FIFO-NEW', 'expiry_date' => now()->addDays(10)->toDateString(), 'reference_type' => 'T', 'reference_id' => 142]);

        $allocs = $svc->consume($wh->id, 'ingredient', $ing->id, 5, ['movement_type' => 'STOCK_OUT', 'reference_type' => 'T', 'reference_id' => 143]);
        $this->assertEquals($old->id, $allocs[0]['batch_id']);
    }

    public function test_two_factor_enable_flow(): void
    {
        ['org' => $org] = $this->baseFixtures();
        $admin = $this->makeUser($org, 'admin', 'tfa2@mbg.id');

        $this->actingAs($admin)->get('/profile')->assertOk();
        $secret = $admin->fresh()->two_factor_secret;
        $this->assertNotEmpty($secret);
        $code = app(TotpService::class)->code($secret);
        $this->actingAs($admin)->post('/profile/2fa/confirm', ['code' => '000000'])->assertSessionHasErrors('code');
        $this->actingAs($admin)->post('/profile/2fa/confirm', ['code' => $code])->assertRedirect();
        $this->assertNotNull($admin->fresh()->two_factor_confirmed_at);
        $this->actingAs($admin)->post('/profile/2fa/disable')->assertRedirect();
        $this->assertNull($admin->fresh()->two_factor_confirmed_at);
    }

    public function test_api_error_envelope(): void
    {
        $resp = $this->getJson('/api/v1/deliveries/999999');
        $resp->assertUnauthorized();
        $resp = $this->postJson('/api/v1/token', ['email' => 'x@y.id', 'password' => 'z'])->assertStatus(422);
        $resp->assertJsonStructure(['success', 'message', 'errors', 'trace_id']);
        $this->assertFalse($resp->json('success'));
    }

    public function test_webhook_events_dispatched(): void
    {
        ['org' => $org, 'ck' => $ck] = $this->baseFixtures();
        $admin = $this->makeUser($org, 'admin', 'whev@mbg.id');
        Http::fake(['*' => Http::response([], 200)]);
        Webhook::create(['organization_id' => $org->id, 'name' => 'T', 'url' => 'https://example.test/h', 'events' => ['purchase.created', 'purchase.approved'], 'is_active' => true]);

        $supplier = Supplier::create(['organization_id' => $org->id, 'code' => 'SUP-WE', 'name' => 'WE', 'status' => 'ACTIVE']);
        $this->actingAs($admin)->post('/purchase-orders', [
            'supplier_id' => $supplier->id, 'payment_terms' => 'CASH',
            'items' => [['ingredient_id' => $this->makeIngredient($org, $this->makeUnit(), 'ING-WE')->id, 'qty' => 5, 'price' => 1000]],
        ])->assertRedirect();
        $this->assertDatabaseHas('webhook_deliveries', ['event' => 'purchase.created']);
    }

    public function test_reconcile_and_health_and_escalate(): void
    {
        ['org' => $org] = $this->baseFixtures();
        $admin = $this->makeUser($org, 'admin', 'ops@mbg.id');

        $this->actingAs($admin)->get('/inventory/reconcile')->assertOk();
        $this->actingAs($admin)->get('/health')->assertOk()->assertSee('Scheduler');
        $this->artisan('mbg:escalate')->assertSuccessful();
        $this->artisan('mbg:cleanup')->assertSuccessful();
        $this->artisan('mbg:backup')->assertSuccessful();
    }

    public function test_import_ingredients_and_schools(): void
    {
        ['org' => $org] = $this->baseFixtures();
        $admin = $this->makeUser($org, 'admin', 'imp2@mbg.id');

        $csv = "code,name,category,unit_code,price,min_stock\nING-IM1,Kecap,SPICE,KG,20000,5\n";
        $tmp = tempnam(sys_get_temp_dir(), 'imp').'.csv';
        file_put_contents($tmp, $csv);
        $this->actingAs($admin)->post('/imports/ingredients/preview', ['file' => new UploadedFile($tmp, 'i.csv', 'text/csv', null, true)])->assertOk()->assertSee('Commit');
        @unlink($tmp);

        $csv2 = "code,name,level,target_portions,district\nSCH-IM9,SD Impor,SD,100,Cipayung\n";
        $tmp2 = tempnam(sys_get_temp_dir(), 'imp').'.csv';
        file_put_contents($tmp2, $csv2);
        $this->actingAs($admin)->post('/imports/schools/preview', ['file' => new UploadedFile($tmp2, 's.csv', 'text/csv', null, true)])->assertOk();
        @unlink($tmp2);
        $this->actingAs($admin)->get('/imports')->assertOk();
    }

    public function test_new_pages_render(): void
    {
        ['org' => $org] = $this->baseFixtures();
        $admin = $this->makeUser($org, 'admin', 'pages2@mbg.id');
        foreach (['/reports/daily', '/reports/exceptions', '/automation', '/documents', '/documents/create', '/imports', '/search?q=test', '/profile', '/health', '/forecasts', '/hygiene', '/hygiene/create', '/capacity', '/tms/tower', '/analytics', '/recalls', '/trace', '/boms/compare', '/mrp', '/rfqs', '/invoices', '/returns', '/budgets', '/closings', '/approvals/matrix', '/approvals/delegations', '/webhooks', '/notifications/preferences', '/notifications/templates', '/portal', '/costings/history', '/reports/intelligence', '/reports/ap-aging', '/reports/school-cost'] as $page) {
            $this->actingAs($admin)->get($page)->assertOk($page);
        }
    }

    public function test_delivery_returns_page_and_restock_flow(): void
    {
        ['org' => $org, 'ck' => $ck, 'wh' => $wh, 'unit' => $unit, 'prd' => $prd] = $this->baseFixtures();
        $admin = $this->makeUser($org, 'admin', 'rtn2@mbg.id');
        app(InventoryService::class)->produceOutput(['organization_id' => $org->id, 'warehouse_id' => $wh->id, 'item_id' => $prd->id, 'qty' => 20, 'unit_id' => $unit->id, 'unit_cost' => 15000, 'batch_no' => 'RTN2-P1', 'reference_type' => 'T', 'reference_id' => 151]);
        $school = School::create(['organization_id' => $org->id, 'central_kitchen_id' => $ck->id, 'code' => 'SCH-RT2', 'name' => 'SD RT2', 'level' => 'SD', 'student_count' => 20, 'target_portions' => 20, 'status' => 'ACTIVE']);
        $delivery = Delivery::create(['organization_id' => $org->id, 'central_kitchen_id' => $ck->id, 'school_id' => $school->id, 'number' => 'DLV-RT2-1', 'delivery_date' => now()->toDateString(), 'qty_planned' => 20, 'status' => 'IN_TRANSIT']);
        $delivery->items()->create(['product_id' => $prd->id, 'qty_planned' => 20]);
        app(InventoryService::class)->consume($wh->id, 'product', $prd->id, 20, ['organization_id' => $org->id, 'movement_type' => 'DELIVERY', 'reference_type' => Delivery::class, 'reference_id' => $delivery->id, 'reference_no' => $delivery->number]);

        $this->actingAs($admin)->get('/returns')->assertOk();
        $this->actingAs($admin)->get("/deliveries/{$delivery->id}/returns/create")->assertOk();
    }
}
