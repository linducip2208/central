<?php

namespace Tests\Feature;

use App\Models\Downtime;
use App\Models\ProductionOrder;
use App\Models\WorkCenter;
use App\Services\CapacityService;
use App\Services\InventoryService;
use Tests\TestCase;

class MesTest extends TestCase
{
    public function test_material_check_theoretical_and_shortage(): void
    {
        ['org' => $org, 'ck' => $ck, 'wh' => $wh, 'unit' => $unit, 'ing' => $ing, 'prd' => $prd] = $this->baseFixtures();
        $admin = $this->makeUser($org, 'admin', 'mes@mbg.id');
        $ing->update(['standard_price' => 10000]);
        // Stok hanya 5, butuh 30 → gagal dengan rincian.
        app(InventoryService::class)->receive(['organization_id' => $org->id, 'warehouse_id' => $wh->id, 'item_type' => 'ingredient', 'item_id' => $ing->id, 'qty' => 5, 'unit_id' => $unit->id, 'unit_cost' => 10000, 'batch_no' => 'MES-B1', 'reference_type' => 'T', 'reference_id' => 111]);
        $order = ProductionOrder::create(['organization_id' => $org->id, 'central_kitchen_id' => $ck->id, 'product_id' => $prd->id, 'number' => 'WO-MES-1', 'production_date' => now()->toDateString(), 'planned_qty' => 100, 'unit_id' => $unit->id, 'status' => 'RELEASED']);
        $order->items()->create(['ingredient_id' => $ing->id, 'qty_required' => 30, 'unit_id' => $unit->id]);

        $this->actingAs($admin)->post("/production-orders/{$order->id}/material-check")->assertRedirect();
        $this->assertEquals('PENDING', $order->fresh()->material_status);
        $this->assertEquals(30 * 10000, (float) $order->fresh()->theoretical_cost);
    }

    public function test_operators_workcenter_downtime(): void
    {
        ['org' => $org, 'ck' => $ck, 'unit' => $unit, 'prd' => $prd] = $this->baseFixtures();
        $admin = $this->makeUser($org, 'admin', 'mes2@mbg.id');
        $wc = WorkCenter::create(['central_kitchen_id' => $ck->id, 'code' => 'WC-T1', 'name' => 'Tungku 1', 'center_type' => 'COOKING', 'capacity_per_hour' => 500, 'status' => 'ACTIVE']);
        $order = ProductionOrder::create(['organization_id' => $org->id, 'central_kitchen_id' => $ck->id, 'product_id' => $prd->id, 'number' => 'WO-MES-2', 'production_date' => now()->toDateString(), 'planned_qty' => 100, 'unit_id' => $unit->id, 'status' => 'RELEASED']);

        $this->actingAs($admin)->post("/production-orders/{$order->id}/work-center", ['work_center_id' => $wc->id])->assertRedirect();
        $this->actingAs($admin)->post("/production-orders/{$order->id}/operator", ['user_id' => $admin->id, 'role' => 'SUPERVISOR'])->assertRedirect();
        $this->actingAs($admin)->post("/production-orders/{$order->id}/downtime", [
            'reason' => 'Gas habis', 'started_at' => now()->subHour()->format('Y-m-d\TH:i'), 'ended_at' => now()->format('Y-m-d\TH:i'),
        ])->assertRedirect();

        $this->assertEquals('Tungku 1', $order->fresh()->workCenter->name);
        $this->assertEquals(1, $order->operators()->count());
        $this->assertEquals(60, (int) $order->fresh()->id && Downtime::where('production_order_id', $order->id)->first()->durationMinutes());
        $this->actingAs($admin)->get("/production-orders/{$order->id}")->assertOk()->assertSee('Gas habis');
    }

    public function test_capacity_flags_overload(): void
    {
        ['org' => $org, 'ck' => $ck, 'unit' => $unit, 'prd' => $prd] = $this->baseFixtures();
        WorkCenter::create(['central_kitchen_id' => $ck->id, 'code' => 'WC-C1', 'name' => 'C1', 'center_type' => 'COOKING', 'capacity_per_hour' => 100, 'status' => 'ACTIVE']);
        // Kapasitas harian = 800; rencanakan 5000 → OVERLOAD.
        ProductionOrder::create(['organization_id' => $org->id, 'central_kitchen_id' => $ck->id, 'product_id' => $prd->id, 'number' => 'WO-CAP-1', 'production_date' => now()->toDateString(), 'planned_qty' => 5000, 'unit_id' => $unit->id, 'status' => 'RELEASED']);

        $days = app(CapacityService::class)->dailyLoad($ck->id, now()->toDateString(), now()->toDateString());
        $this->assertEquals('OVERLOAD', $days[0]['flag']);
        $this->assertGreaterThan(100, $days[0]['utilization']);
    }
}
