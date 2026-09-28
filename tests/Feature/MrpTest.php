<?php

namespace Tests\Feature;

use App\Models\Bom;
use App\Models\DemandPlan;
use App\Models\Supplier;
use App\Models\SupplierPriceList;
use App\Services\InventoryService;
use App\Services\MrpService;
use Tests\TestCase;

class MrpTest extends TestCase
{
    protected function seedPrice(int $orgId, int $ingId, int $unitId): void
    {
        $sup = Supplier::create(['organization_id' => $orgId, 'code' => 'SUP-MRP-'.uniqid(), 'name' => 'MRP Sup', 'status' => 'ACTIVE']);
        SupplierPriceList::create(['supplier_id' => $sup->id, 'ingredient_id' => $ingId, 'price' => 10000, 'unit_id' => $unitId, 'moq' => 0, 'lead_time_days' => 1]);
    }

    public function test_mrp_netting_with_explanation(): void
    {
        ['org' => $org, 'ck' => $ck, 'wh' => $wh, 'unit' => $unit, 'ing' => $ing, 'prd' => $prd] = $this->baseFixtures();
        $admin = $this->makeUser($org, 'admin', 'mrp@mbg.id');
        $ing->update(['safety_stock' => 100, 'lead_time_days' => 0, 'moq' => 0]);
        $this->seedPrice($org->id, $ing->id, $unit->id);

        // BOM: 1 porsi butuh 0.15 bahan (yield 1).
        $bom = Bom::create(['organization_id' => $org->id, 'item_type' => 'product', 'item_id' => $prd->id, 'code' => 'BOM-MRP-'.uniqid(), 'version' => '1.0', 'yield_qty' => 1, 'status' => 'ACTIVE']);
        $bom->items()->create(['component_type' => 'ingredient', 'component_id' => $ing->id, 'qty' => 0.15, 'unit_id' => $unit->id, 'level' => 1]);

        // Stok 700 dari gross 1250 → net = 1250 - 700 + 100 safety = 650.
        app(InventoryService::class)->receive([
            'organization_id' => $org->id, 'warehouse_id' => $wh->id,
            'item_type' => 'ingredient', 'item_id' => $ing->id,
            'qty' => 700, 'unit_id' => $unit->id, 'unit_cost' => 10000,
            'batch_no' => 'MRP-B1', 'reference_type' => 'T', 'reference_id' => 91,
        ]);

        $plan = DemandPlan::create(['organization_id' => $org->id, 'central_kitchen_id' => $ck->id, 'number' => 'DP-T1', 'period_type' => 'DAILY', 'period_start' => now()->toDateString(), 'period_end' => now()->toDateString(), 'status' => 'APPROVED', 'created_by' => $admin->id]);
        $plan->lines()->create(['product_id' => $prd->id, 'demand_date' => now()->toDateString(), 'gross_demand' => 10000, 'adjusted_demand' => 10000, 'net_demand' => 10000, 'source' => 'MANUAL']);

        $run = app(MrpService::class)->run($plan, $wh->id, $admin->id);
        $line = $run->lines()->first();

        // Gross 1500 − tersedia 700 + safety 100 → net 900 + penjelasan.
        $this->assertNotNull($line);
        $this->assertEquals(10000 * 0.15, (float) $line->gross_requirement);
        $this->assertEquals(700, (float) $line->on_hand);
        $this->assertEquals(10000 * 0.15 - 700 + 100, (float) $line->net_requirement);
        $this->assertEquals('PURCHASE', $line->recommendation);
        $this->assertStringContainsString('Butuh', $line->explanation);
    }

    public function test_mrp_no_purchase_when_covered(): void
    {
        ['org' => $org, 'ck' => $ck, 'wh' => $wh, 'unit' => $unit, 'ing' => $ing, 'prd' => $prd] = $this->baseFixtures();
        $admin = $this->makeUser($org, 'admin', 'mrp2@mbg.id');
        app(InventoryService::class)->receive([
            'organization_id' => $org->id, 'warehouse_id' => $wh->id,
            'item_type' => 'ingredient', 'item_id' => $ing->id,
            'qty' => 5000, 'unit_id' => $unit->id, 'unit_cost' => 10000,
            'batch_no' => 'MRP-B2', 'reference_type' => 'T', 'reference_id' => 92,
        ]);
        $bom = Bom::create(['organization_id' => $org->id, 'item_type' => 'product', 'item_id' => $prd->id, 'code' => 'BOM-MRP2-'.uniqid(), 'version' => '1.0', 'yield_qty' => 1, 'status' => 'ACTIVE']);
        $bom->items()->create(['component_type' => 'ingredient', 'component_id' => $ing->id, 'qty' => 0.1, 'unit_id' => $unit->id, 'level' => 1]);

        $plan = DemandPlan::create(['organization_id' => $org->id, 'central_kitchen_id' => $ck->id, 'number' => 'DP-T2', 'period_type' => 'DAILY', 'period_start' => now()->toDateString(), 'period_end' => now()->toDateString(), 'status' => 'APPROVED', 'created_by' => $admin->id]);
        $plan->lines()->create(['product_id' => $prd->id, 'demand_date' => now()->toDateString(), 'gross_demand' => 100, 'adjusted_demand' => 100, 'net_demand' => 100, 'source' => 'MANUAL']);

        $run = app(MrpService::class)->run($plan, $wh->id, $admin->id);
        $this->assertEquals('NONE', $run->lines()->first()->recommendation);
        $this->assertEquals(0, (float) $run->lines()->first()->net_requirement);
    }

    public function test_mrp_shortage_without_supplier(): void
    {
        ['org' => $org, 'ck' => $ck, 'wh' => $wh, 'unit' => $unit, 'ing' => $ing, 'prd' => $prd] = $this->baseFixtures();
        $admin = $this->makeUser($org, 'admin', 'mrp4@mbg.id');
        $bom = Bom::create(['organization_id' => $org->id, 'item_type' => 'product', 'item_id' => $prd->id, 'code' => 'BOM-MRP4-'.uniqid(), 'version' => '1.0', 'yield_qty' => 1, 'status' => 'ACTIVE']);
        $bom->items()->create(['component_type' => 'ingredient', 'component_id' => $ing->id, 'qty' => 1, 'unit_id' => $unit->id, 'level' => 1]);

        $plan = DemandPlan::create(['organization_id' => $org->id, 'central_kitchen_id' => $ck->id, 'number' => 'DP-T4', 'period_type' => 'DAILY', 'period_start' => now()->toDateString(), 'period_end' => now()->toDateString(), 'status' => 'APPROVED', 'created_by' => $admin->id]);
        $plan->lines()->create(['product_id' => $prd->id, 'demand_date' => now()->toDateString(), 'gross_demand' => 50, 'adjusted_demand' => 50, 'net_demand' => 50, 'source' => 'MANUAL']);
        $run = app(MrpService::class)->run($plan, $wh->id, $admin->id);
        $this->assertEquals('SHORTAGE', $run->lines()->first()->recommendation);
    }

    public function test_mrp_to_pr_creates_draft(): void
    {
        ['org' => $org, 'ck' => $ck, 'wh' => $wh, 'unit' => $unit, 'ing' => $ing, 'prd' => $prd] = $this->baseFixtures();
        $admin = $this->makeUser($org, 'admin', 'mrp3@mbg.id');
        $this->seedPrice($org->id, $ing->id, $unit->id);
        $bom = Bom::create(['organization_id' => $org->id, 'item_type' => 'product', 'item_id' => $prd->id, 'code' => 'BOM-MRP3-'.uniqid(), 'version' => '1.0', 'yield_qty' => 1, 'status' => 'ACTIVE']);
        $bom->items()->create(['component_type' => 'ingredient', 'component_id' => $ing->id, 'qty' => 1, 'unit_id' => $unit->id, 'level' => 1]);

        $plan = DemandPlan::create(['organization_id' => $org->id, 'central_kitchen_id' => $ck->id, 'number' => 'DP-T3', 'period_type' => 'DAILY', 'period_start' => now()->toDateString(), 'period_end' => now()->toDateString(), 'status' => 'APPROVED', 'created_by' => $admin->id]);
        $plan->lines()->create(['product_id' => $prd->id, 'demand_date' => now()->toDateString(), 'gross_demand' => 50, 'adjusted_demand' => 50, 'net_demand' => 50, 'source' => 'MANUAL']);
        $run = app(MrpService::class)->run($plan, $wh->id, $admin->id);
        $lineId = $run->lines()->first()->id;

        $this->actingAs($admin)->post("/mrp/{$run->id}/to-pr", ['line_ids' => [$lineId]])->assertRedirect();
        $this->assertDatabaseHas('purchase_requests', ['notes' => 'Dari MRP '.$run->number]);
    }
}
