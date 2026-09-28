<?php

namespace Tests\Feature;

use App\Models\Bom;
use App\Services\BomService;
use Tests\TestCase;

class BomTest extends TestCase
{
    protected function makeBom(array $overrides = []): Bom
    {
        ['org' => $org, 'unit' => $unit, 'ing' => $ing, 'prd' => $prd] = $this->baseFixtures();

        return Bom::create(array_merge([
            'organization_id' => $org->id, 'item_type' => 'product', 'item_id' => $prd->id,
            'code' => 'BOM-T-'.uniqid(), 'version' => '1.0',
            'effective_from' => now()->subDay()->toDateString(),
            'yield_qty' => 100, 'status' => 'ACTIVE',
        ], $overrides));
    }

    public function test_explosion_scales_with_scrap_and_waste(): void
    {
        ['unit' => $unit, 'ing' => $ing] = $this->baseFixtures();
        $bom = $this->makeBom();
        $bom->items()->create(['component_type' => 'ingredient', 'component_id' => $ing->id, 'qty' => 10, 'unit_id' => $unit->id, 'scrap_pct' => 10, 'waste_pct' => 0, 'level' => 1]);

        // yield 100, minta 200 → 20 + 10% scrap = 22
        $needs = app(BomService::class)->explode($bom->item_id, 200);
        $this->assertCount(1, $needs);
        $this->assertEquals(22, $needs[0]['qty']);
    }

    public function test_multilevel_explosion(): void
    {
        ['org' => $org, 'unit' => $unit, 'ing' => $ing, 'prd' => $prd] = $this->baseFixtures();
        $sub = $this->makeProduct($org, $unit, 'SUB1');
        $subBom = Bom::create(['organization_id' => $org->id, 'item_type' => 'product', 'item_id' => $sub->id, 'code' => 'BOM-SUB-'.uniqid(), 'version' => '1.0', 'yield_qty' => 10, 'status' => 'ACTIVE']);
        $subBom->items()->create(['component_type' => 'ingredient', 'component_id' => $ing->id, 'qty' => 5, 'unit_id' => $unit->id, 'level' => 1]);

        $top = $this->makeBom();
        $top->items()->create(['component_type' => 'product', 'component_id' => $sub->id, 'qty' => 2, 'unit_id' => $unit->id, 'level' => 1]);

        // Minta 100 (yield top 100) → 2 sub × (5/10 per sub) = 1.0? hitung: multiplier top = 100/100 = 1 → need sub = 2 → sub multiplier = 2/10 = 0.2 → ing = 5*0.2 = 1
        $needs = app(BomService::class)->explode($prd->id, 100);
        $this->assertEquals(1, $needs[0]['qty']);
    }

    public function test_cycle_detection_on_explode(): void
    {
        ['org' => $org, 'unit' => $unit, 'prd' => $prd] = $this->baseFixtures();
        $bom = $this->makeBom();
        // Produk mereferensikan dirinya sendiri sebagai sub-assembly.
        $bom->items()->create(['component_type' => 'product', 'component_id' => $prd->id, 'qty' => 1, 'unit_id' => $unit->id, 'level' => 1]);

        $this->expectException(\RuntimeException::class);
        app(BomService::class)->explode($prd->id, 10);
    }

    public function test_assert_no_cycle_rejects_self_reference(): void
    {
        $bom = $this->makeBom();
        $this->expectException(\InvalidArgumentException::class);
        app(BomService::class)->assertNoCycle($bom, 'product', $bom->item_id);
    }

    public function test_version_selection_effective_date(): void
    {
        ['org' => $org, 'unit' => $unit, 'ing' => $ing, 'prd' => $prd] = $this->baseFixtures();
        $old = Bom::create(['organization_id' => $org->id, 'item_type' => 'product', 'item_id' => $prd->id, 'code' => 'BOM-V1-'.uniqid(), 'version' => '1.0', 'effective_from' => now()->subDays(60)->toDateString(), 'effective_to' => now()->subDays(30)->toDateString(), 'yield_qty' => 1, 'status' => 'ACTIVE']);
        $new = Bom::create(['organization_id' => $org->id, 'item_type' => 'product', 'item_id' => $prd->id, 'code' => 'BOM-V2-'.uniqid(), 'version' => '2.0', 'effective_from' => now()->subDays(10)->toDateString(), 'yield_qty' => 1, 'status' => 'ACTIVE']);

        $this->assertEquals($new->id, Bom::activeForProduct($prd->id)->id);
        $this->assertEquals($old->id, Bom::activeForProduct($prd->id, now()->subDays(40)->toDateString())->id);
    }

    public function test_bom_approve_archives_old_version(): void
    {
        ['org' => $org] = $this->baseFixtures();
        $admin = $this->makeUser($org, 'admin', 'bom@mbg.id');
        $old = $this->makeBom(['code' => 'BOM-A-'.uniqid()]);
        $new = $this->makeBom(['code' => 'BOM-B-'.uniqid(), 'status' => 'DRAFT']);

        $this->actingAs($admin)->post("/boms/{$new->id}/approve")->assertRedirect();
        $this->assertEquals('ARCHIVED', $old->fresh()->status);
        $this->assertEquals('ACTIVE', $new->fresh()->status);
    }
}
