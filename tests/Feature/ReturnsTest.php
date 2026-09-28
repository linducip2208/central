<?php

namespace Tests\Feature;

use App\Models\Delivery;
use App\Models\DeliveryReturn;
use App\Models\InventoryMovement;
use App\Models\School;
use App\Services\InventoryService;
use Tests\TestCase;

class ReturnsTest extends TestCase
{
    protected function seedDelivery(): array
    {
        ['org' => $org, 'ck' => $ck, 'wh' => $wh, 'unit' => $unit, 'prd' => $prd] = $this->baseFixtures();
        $admin = $this->makeUser($org, 'admin', 'rtn@mbg.id');
        app(InventoryService::class)->produceOutput(['organization_id' => $org->id, 'warehouse_id' => $wh->id, 'item_id' => $prd->id, 'qty' => 50, 'unit_id' => $unit->id, 'unit_cost' => 15000, 'batch_no' => 'RTN-P1', 'reference_type' => 'T', 'reference_id' => 121]);
        $school = School::create(['organization_id' => $org->id, 'central_kitchen_id' => $ck->id, 'code' => 'SCH-RT', 'name' => 'SD RT', 'level' => 'SD', 'student_count' => 50, 'target_portions' => 50, 'status' => 'ACTIVE']);
        $delivery = Delivery::create(['organization_id' => $org->id, 'central_kitchen_id' => $ck->id, 'school_id' => $school->id, 'number' => 'DLV-RT-1', 'delivery_date' => now()->toDateString(), 'qty_planned' => 50, 'status' => 'IN_TRANSIT']);
        $delivery->items()->create(['product_id' => $prd->id, 'qty_planned' => 50]);
        // Serah terima 50 agar stok keluar dan qty tercatat.
        app(InventoryService::class)->consume($wh->id, 'product', $prd->id, 50, ['organization_id' => $org->id, 'movement_type' => 'DELIVERY', 'reference_type' => Delivery::class, 'reference_id' => $delivery->id, 'reference_no' => $delivery->number]);

        return compact('org', 'ck', 'wh', 'unit', 'prd', 'admin', 'delivery');
    }

    public function test_good_return_restocks(): void
    {
        ['wh' => $wh, 'prd' => $prd, 'admin' => $admin, 'delivery' => $delivery] = $this->seedDelivery();
        $svc = app(InventoryService::class);
        $this->assertEquals(0, $svc->stockOf($wh->id, 'product', $prd->id));

        $this->actingAs($admin)->post("/deliveries/{$delivery->id}/returns", [
            'warehouse_id' => $wh->id,
            'items' => [['product_id' => $prd->id, 'qty' => 5, 'reason' => 'EXCESS', 'condition' => 'GOOD']],
        ])->assertRedirect();
        $ret = DeliveryReturn::latest()->first();
        $this->assertEquals(5, $delivery->fresh()->qty_returned);

        $this->actingAs($admin)->post("/returns/{$ret->id}/restock")->assertRedirect();
        $this->assertEquals('RESTOCKED', $ret->fresh()->disposition);
        $this->assertEquals(5, $svc->stockOf($wh->id, 'product', $prd->id));
        $this->assertEquals(1, InventoryMovement::where('movement_type', 'RETURN')->count());
    }

    public function test_damaged_return_becomes_waste(): void
    {
        ['wh' => $wh, 'prd' => $prd, 'admin' => $admin, 'delivery' => $delivery] = $this->seedDelivery();
        $this->actingAs($admin)->post("/deliveries/{$delivery->id}/returns", [
            'warehouse_id' => $wh->id,
            'items' => [['product_id' => $prd->id, 'qty' => 3, 'reason' => 'DAMAGED', 'condition' => 'DAMAGED']],
        ])->assertRedirect();
        $ret = DeliveryReturn::latest()->first();

        // Kondisi rusak tidak boleh restock.
        $this->actingAs($admin)->post("/returns/{$ret->id}/restock")->assertStatus(422);
        $this->actingAs($admin)->post("/returns/{$ret->id}/waste")->assertRedirect();
        $this->assertEquals('WASTED', $ret->fresh()->disposition);
        $this->assertDatabaseHas('wastes', ['item_id' => $prd->id, 'qty' => 3]);
    }

    public function test_return_pages_render(): void
    {
        ['admin' => $admin, 'delivery' => $delivery] = $this->seedDelivery();
        $this->actingAs($admin)->get('/returns')->assertOk();
        $this->actingAs($admin)->get("/deliveries/{$delivery->id}/returns/create")->assertOk();
    }
}
