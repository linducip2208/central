<?php

namespace Tests\Unit;

use App\Models\Recipe;
use App\Models\UnitConversion;
use Tests\TestCase;

class UnitConversionTest extends TestCase
{
    public function test_direct_conversion(): void
    {
        $kg = $this->makeUnit('KG');
        $g = $this->makeUnit('G');
        UnitConversion::create(['from_unit_id' => $kg->id, 'to_unit_id' => $g->id, 'factor' => 1000]);

        $this->assertEquals(2000, $kg->convertTo(2, $g->id));
        $this->assertEquals(2, $g->convertTo(2000, $kg->id)); // reverse path
        $this->assertEquals(5, $kg->convertTo(5, $kg->id)); // identity
    }

    public function test_missing_conversion_returns_null(): void
    {
        $a = $this->makeUnit('UAA');
        $b = $this->makeUnit('UBB');
        $this->assertNull($a->convertTo(1, $b->id));
    }

    public function test_recipe_required_for_scales_with_waste(): void
    {
        ['org' => $org, 'unit' => $unit, 'ing' => $ing, 'prd' => $prd] = $this->baseFixtures();
        $recipe = Recipe::create(['organization_id' => $org->id, 'product_id' => $prd->id, 'code' => 'R-U1', 'name' => 'R', 'yield_qty' => 100, 'yield_unit_id' => $unit->id, 'is_active' => true]);
        $item = $recipe->items()->create(['ingredient_id' => $ing->id, 'qty' => 15, 'unit_id' => $unit->id, 'waste_factor_pct' => 10]);

        // 200 porsi dari yield 100 → 30 + 10% susut = 33
        $this->assertEquals(33, $item->requiredFor(200, 100));
        $this->assertEquals(0, $item->requiredFor(200, 0));
    }
}
