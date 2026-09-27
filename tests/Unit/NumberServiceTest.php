<?php

namespace Tests\Unit;

use App\Models\Costing;
use App\Services\NumberService;
use Tests\TestCase;

class NumberServiceTest extends TestCase
{
    public function test_numbers_increment_per_prefix_and_period(): void
    {
        $svc = app(NumberService::class);
        $a = $svc->next('PR');
        $b = $svc->next('PR');
        $c = $svc->next('PO');

        $this->assertNotEquals($a, $b);
        $this->assertStringStartsWith('PR-'.now()->format('Ym').'-', $a);
        $this->assertStringStartsWith('PO-'.now()->format('Ym').'-', $c);
        $this->assertGreaterThan((int) substr($a, -4), (int) substr($b, -4));
    }

    public function test_costing_auto_totals(): void
    {
        ['org' => $org, 'ck' => $ck] = $this->baseFixtures();
        $c = Costing::create([
            'organization_id' => $org->id, 'central_kitchen_id' => $ck->id,
            'costing_date' => now()->toDateString(), 'material_cost' => 100000,
            'labor_cost' => 50000, 'overhead_cost' => 25000, 'packaging_cost' => 10000,
            'delivery_cost' => 15000, 'portions' => 100,
        ]);
        $this->assertEquals(200000, (float) $c->total_cost);
        $this->assertEquals(2000, (float) $c->cost_per_portion);
    }
}
