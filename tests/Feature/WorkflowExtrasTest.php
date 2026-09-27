<?php

namespace Tests\Feature;

use App\Models\Delivery;
use App\Models\PurchaseOrder;
use App\Models\Recipe;
use App\Models\School;
use App\Models\Supplier;
use App\Models\Unit;
use App\Services\InventoryService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WorkflowExtrasTest extends TestCase
{
    public function test_unit_crud_and_conversion(): void
    {
        ['org' => $org] = $this->baseFixtures();
        $admin = $this->makeUser($org, 'admin', 'unit@mbg.id');

        $this->actingAs($admin)->post('/units', [
            'code' => 'SAK', 'name' => 'Karung', 'symbol' => 'sak', 'unit_type' => 'COUNT',
        ])->assertRedirect();
        $sak = Unit::where('code', 'SAK')->firstOrFail();

        $kg = Unit::where('code', 'KG')->firstOrFail();
        $this->actingAs($admin)->post("/units/{$sak->id}/conversions", [
            'to_unit_id' => $kg->id, 'factor' => 50,
        ])->assertRedirect();
        $this->assertEquals(100, $sak->fresh()->convertTo(2, $kg->id));

        $this->actingAs($admin)->get('/units')->assertOk()->assertSee('Karung');
    }

    public function test_transfer_via_http(): void
    {
        ['org' => $org, 'ck' => $ck, 'wh' => $wh, 'unit' => $unit, 'ing' => $ing] = $this->baseFixtures();
        $admin = $this->makeUser($org, 'admin', 'trf@mbg.id');
        $wh2 = $this->makeWarehouse($ck, 'WHT');
        $svc = app(InventoryService::class);
        $svc->receive(['organization_id' => $org->id, 'warehouse_id' => $wh->id, 'item_type' => 'ingredient', 'item_id' => $ing->id, 'qty' => 20, 'unit_id' => $unit->id, 'unit_cost' => 5000, 'batch_no' => 'TRF-B1', 'reference_type' => 'T', 'reference_id' => 71]);

        $this->actingAs($admin)->post('/inventory/transfer', [
            'from_warehouse_id' => $wh->id, 'to_warehouse_id' => $wh2->id,
            'ingredient_id' => $ing->id, 'qty' => 8, 'notes' => 'test',
        ])->assertRedirect('/inventory');

        $this->assertEquals(12, $svc->stockOf($wh->id, 'ingredient', $ing->id));
        $this->assertEquals(8, $svc->stockOf($wh2->id, 'ingredient', $ing->id));
    }

    public function test_transfer_same_warehouse_rejected(): void
    {
        ['org' => $org, 'wh' => $wh, 'ing' => $ing] = $this->baseFixtures();
        $admin = $this->makeUser($org, 'admin', 'trf2@mbg.id');

        $this->actingAs($admin)->post('/inventory/transfer', [
            'from_warehouse_id' => $wh->id, 'to_warehouse_id' => $wh->id,
            'ingredient_id' => $ing->id, 'qty' => 1,
        ])->assertSessionHasErrors();
    }

    public function test_reserve_and_release_via_http(): void
    {
        ['org' => $org, 'wh' => $wh, 'unit' => $unit, 'ing' => $ing] = $this->baseFixtures();
        $admin = $this->makeUser($org, 'admin', 'rsv@mbg.id');
        $svc = app(InventoryService::class);
        $svc->receive(['organization_id' => $org->id, 'warehouse_id' => $wh->id, 'item_type' => 'ingredient', 'item_id' => $ing->id, 'qty' => 10, 'unit_id' => $unit->id, 'unit_cost' => 5000, 'batch_no' => 'RSV-B1', 'reference_type' => 'T', 'reference_id' => 72]);

        $this->actingAs($admin)->post('/inventory/reserve', [
            'warehouse_id' => $wh->id, 'item_type' => 'ingredient', 'item_id' => $ing->id, 'qty' => 6,
        ])->assertRedirect();
        $this->assertEquals(4, $svc->availableOf($wh->id, 'ingredient', $ing->id));

        $this->actingAs($admin)->post('/inventory/release', [
            'warehouse_id' => $wh->id, 'item_type' => 'ingredient', 'item_id' => $ing->id, 'qty' => 6,
        ])->assertRedirect();
        $this->assertEquals(10, $svc->availableOf($wh->id, 'ingredient', $ing->id));
    }

    public function test_recipients_page_and_allergy_filter(): void
    {
        ['org' => $org, 'ck' => $ck] = $this->baseFixtures();
        $admin = $this->makeUser($org, 'admin', 'rcp@mbg.id');
        $school = School::create(['organization_id' => $org->id, 'central_kitchen_id' => $ck->id, 'code' => 'SCH-R1', 'name' => 'SD R', 'level' => 'SD', 'student_count' => 50, 'target_portions' => 50, 'status' => 'ACTIVE']);
        $school->recipients()->create(['name' => 'Alergi Susu', 'allergy_notes' => 'susu sapi', 'is_active' => true]);
        $school->recipients()->create(['name' => 'Biasa', 'is_active' => true]);

        $this->actingAs($admin)->get('/recipients')->assertOk()->assertSee('Alergi Susu');
        $this->actingAs($admin)->get('/recipients?allergy=1')->assertOk()->assertSee('Alergi Susu')->assertDontSee('Biasa');
    }

    public function test_recipe_nutrition_saved(): void
    {
        ['org' => $org, 'unit' => $unit, 'ing' => $ing, 'prd' => $prd] = $this->baseFixtures();
        $admin = $this->makeUser($org, 'admin', 'nut@mbg.id');
        $recipe = Recipe::create(['organization_id' => $org->id, 'product_id' => $prd->id, 'code' => 'R-N1', 'name' => 'R Nutrisi', 'yield_qty' => 1, 'yield_unit_id' => $unit->id, 'is_active' => true]);

        $this->actingAs($admin)->post("/recipes/{$recipe->id}/nutrition", [
            'calories' => 550, 'protein_g' => 20, 'carbs_g' => 70, 'fat_g' => 15,
        ])->assertRedirect();
        $this->assertEquals(550, (float) $recipe->nutrition()->first()->calories);
        $this->actingAs($admin)->get("/recipes/{$recipe->id}")->assertOk()->assertSee('550');
    }

    public function test_delivery_proof_upload_validation(): void
    {
        ['org' => $org, 'ck' => $ck, 'wh' => $wh, 'unit' => $unit, 'prd' => $prd] = $this->baseFixtures();
        $admin = $this->makeUser($org, 'admin', 'prf@mbg.id');
        $school = School::create(['organization_id' => $org->id, 'central_kitchen_id' => $ck->id, 'code' => 'SCH-P1', 'name' => 'SD P', 'level' => 'SD', 'student_count' => 50, 'target_portions' => 50, 'status' => 'ACTIVE']);
        app(InventoryService::class)->produceOutput(['organization_id' => $org->id, 'warehouse_id' => $wh->id, 'item_id' => $prd->id, 'qty' => 50, 'unit_id' => $unit->id, 'unit_cost' => 15000, 'batch_no' => 'PRF-B1', 'reference_type' => 'T', 'reference_id' => 73]);
        $delivery = Delivery::create(['organization_id' => $org->id, 'central_kitchen_id' => $ck->id, 'school_id' => $school->id, 'number' => 'DLV-PRF-1', 'delivery_date' => now()->toDateString(), 'qty_planned' => 50, 'status' => 'IN_TRANSIT']);
        $delivery->items()->create(['product_id' => $prd->id, 'qty_planned' => 50]);

        $file = UploadedFile::fake()->image('bukti.jpg', 800, 600)->size(500);
        $this->actingAs($admin)->post("/deliveries/{$delivery->id}/deliver", [
            'warehouse_id' => $wh->id, 'qty_delivered' => 50, 'received_by_name' => 'Guru',
            'proof' => $file,
        ])->assertRedirect();

        $this->assertNotNull($delivery->fresh()->delivery_proof);
        Storage::disk('public')->assertExists($delivery->fresh()->delivery_proof);
    }

    public function test_workflow_sends_notifications(): void
    {
        ['org' => $org, 'ck' => $ck, 'wh' => $wh, 'unit' => $unit, 'ing' => $ing] = $this->baseFixtures();
        $admin = $this->makeUser($org, 'admin', 'ntf@mbg.id');
        $supplier = Supplier::create(['organization_id' => $org->id, 'code' => 'SUP-N', 'name' => 'Sup N', 'status' => 'ACTIVE']);
        $po = PurchaseOrder::create(['organization_id' => $org->id, 'central_kitchen_id' => $ck->id, 'supplier_id' => $supplier->id, 'warehouse_id' => $wh->id, 'number' => 'PO-NTF-1', 'order_date' => now()->toDateString(), 'status' => 'SUBMITTED']);

        $this->actingAs($admin)->post("/purchase-orders/{$po->id}/approve")->assertRedirect();
        $this->assertTrue($admin->fresh()->notifications()->where('data->type', 'po_approved')->exists());
    }
}
