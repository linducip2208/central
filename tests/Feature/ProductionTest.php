<?php

namespace Tests\Feature;

use App\Models\ProductionOrder;
use App\Models\ProductionPlan;
use App\Models\QualityControl;
use App\Models\Recipe;
use App\Services\CostingService;
use App\Services\Exceptions\InsufficientStockException;
use App\Services\InventoryService;
use Tests\TestCase;

class ProductionTest extends TestCase
{
    public function test_full_production_workflow(): void
    {
        ['org' => $org, 'ck' => $ck, 'wh' => $wh, 'unit' => $unit, 'ing' => $ing, 'prd' => $prd] = $this->baseFixtures();
        $admin = $this->makeUser($org, 'admin', 'prod@test.id');
        $svc = app(InventoryService::class);

        // Stok bahan: 100 kg beras
        $svc->receive(['organization_id' => $org->id, 'warehouse_id' => $wh->id, 'item_type' => 'ingredient', 'item_id' => $ing->id, 'qty' => 100, 'unit_id' => $unit->id, 'unit_cost' => 12000, 'batch_no' => 'BERAS-1', 'reference_type' => 'T', 'reference_id' => 71]);

        // Resep: 1 porsi butuh 0.15 kg (yield 100 porsi)
        $recipe = Recipe::create(['organization_id' => $org->id, 'product_id' => $prd->id, 'code' => 'RCP-1', 'name' => 'Resep 1', 'yield_qty' => 100, 'yield_unit_id' => $unit->id, 'is_active' => true]);
        $recipe->items()->create(['ingredient_id' => $ing->id, 'qty' => 15, 'unit_id' => $unit->id, 'waste_factor_pct' => 0]);

        // Production plan 200 porsi
        $plan = ProductionPlan::create(['organization_id' => $org->id, 'central_kitchen_id' => $ck->id, 'number' => 'PP-1', 'plan_date' => now()->toDateString(), 'target_portions' => 200, 'status' => 'APPROVED', 'created_by' => $admin->id]);
        $plan->items()->create(['product_id' => $prd->id, 'planned_qty' => 200]);

        // Production order 200 porsi → butuh 30 kg
        $order = ProductionOrder::create([
            'organization_id' => $org->id, 'central_kitchen_id' => $ck->id, 'production_plan_id' => $plan->id,
            'product_id' => $prd->id, 'recipe_id' => $recipe->id, 'number' => 'WO-1',
            'production_date' => now()->toDateString(), 'planned_qty' => 200, 'unit_id' => $unit->id, 'status' => 'RELEASED', 'created_by' => $admin->id,
        ]);
        $need = 15 * (200 / 100); // 30
        $order->items()->create(['ingredient_id' => $ing->id, 'qty_required' => $need, 'unit_id' => $unit->id]);

        // Konsumsi bahan (partial ok)
        $allocs = $svc->consume($wh->id, 'ingredient', $ing->id, $need, ['organization_id' => $org->id, 'movement_type' => 'PRODUCTION_CONSUMPTION', 'reference_type' => ProductionOrder::class, 'reference_id' => $order->id, 'reference_no' => $order->number]);
        $order->items()->first()->update(['qty_consumed' => $need]);
        $this->assertNotEmpty($allocs);
        $this->assertEquals(70, $svc->stockOf($wh->id, 'ingredient', $ing->id));

        // Output produksi 195 porsi baik
        $outBatch = $svc->produceOutput(['organization_id' => $org->id, 'warehouse_id' => $wh->id, 'item_id' => $prd->id, 'qty' => 195, 'unit_id' => $unit->id, 'unit_cost' => 15000, 'batch_no' => 'PRD-1', 'reference_type' => ProductionOrder::class, 'reference_id' => $order->id, 'reference_no' => $order->number]);
        $order->update(['produced_qty' => 195, 'status' => 'COMPLETED', 'completed_at' => now()]);
        $this->assertEquals(195, $svc->stockOf($wh->id, 'product', $prd->id));
        $this->assertEquals(97.5, $order->fresh()->completionPct());

        // QC lulus
        $qc = QualityControl::create(['organization_id' => $org->id, 'central_kitchen_id' => $ck->id, 'reference_type' => ProductionOrder::class, 'reference_id' => $order->id, 'number' => 'QC-1', 'check_date' => now()->toDateString(), 'sample_qty' => 10, 'pass_qty' => 10, 'fail_qty' => 0, 'result' => 'PASSED', 'checked_by' => $admin->id]);
        $this->assertEquals('PASSED', $qc->result);

        // Costing dari ledger aktual
        $costing = app(CostingService::class)->forProductionOrder($order->fresh(), ['labor_cost' => 500000]);
        $this->assertEquals(30 * 12000, (float) $costing->material_cost);
        $this->assertEquals(round((30 * 12000 + 500000) / 195, 2), (float) $costing->cost_per_portion);
    }

    public function test_production_fails_without_stock(): void
    {
        ['org' => $org, 'ck' => $ck, 'unit' => $unit, 'ing' => $ing, 'prd' => $prd] = $this->baseFixtures();
        $svc = app(InventoryService::class);
        $order = ProductionOrder::create(['organization_id' => $org->id, 'central_kitchen_id' => $ck->id, 'product_id' => $prd->id, 'number' => 'WO-FAIL', 'production_date' => now()->toDateString(), 'planned_qty' => 10, 'unit_id' => $unit->id, 'status' => 'RELEASED']);

        $this->expectException(InsufficientStockException::class);
        $svc->consume($ck->warehouses()->first()->id ?? 999999, 'ingredient', $ing->id, 50, ['movement_type' => 'PRODUCTION_CONSUMPTION', 'reference_type' => ProductionOrder::class, 'reference_id' => $order->id]);
    }
}
