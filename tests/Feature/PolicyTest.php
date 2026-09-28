<?php

namespace Tests\Feature;

use App\Models\Batch;
use App\Models\Bom;
use App\Models\Delivery;
use App\Models\GoodsReceipt;
use App\Models\ProductionOrder;
use App\Models\PurchaseOrder;
use App\Models\Recall;
use App\Models\Supplier;
use App\Models\SupplierInvoice;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class PolicyTest extends TestCase
{
    public function test_policies_enforce_organization(): void
    {
        ['org' => $org, 'ck' => $ck] = $this->baseFixtures();
        $other = $this->makeOrg('ORGP');
        $insider = $this->makeUser($org, 'warehouse', 'pol-in@mbg.id');
        $outsider = $this->makeUser($other, 'warehouse', 'pol-out@mbg.id');

        $po = PurchaseOrder::create(['organization_id' => $org->id, 'central_kitchen_id' => $ck->id, 'supplier_id' => Supplier::create(['organization_id' => $org->id, 'code' => 'SUP-POL', 'name' => 'P', 'status' => 'ACTIVE'])->id, 'number' => 'PO-POL-1', 'order_date' => now()->toDateString(), 'status' => 'DRAFT']);

        $this->assertTrue(Gate::forUser($insider)->allows('view', $po));
        $this->assertFalse(Gate::forUser($outsider)->allows('view', $po));
        $this->assertFalse(Gate::forUser($outsider)->allows('update', $po));

        foreach ([GoodsReceipt::class, ProductionOrder::class, Delivery::class, Batch::class, Recall::class, SupplierInvoice::class, Bom::class] as $class) {
            $this->assertNotNull(Gate::getPolicyFor($class), "Policy hilang untuk {$class}");
        }
    }
}
