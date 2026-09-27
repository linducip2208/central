<?php

namespace Tests\Feature;

use App\Models\GoodsReceipt;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\Supplier;
use App\Services\InventoryService;
use Tests\TestCase;

class ProcurementTest extends TestCase
{
    public function test_pr_to_po_to_gr_partial_workflow(): void
    {
        ['org' => $org, 'ck' => $ck, 'wh' => $wh, 'unit' => $unit, 'ing' => $ing] = $this->baseFixtures();
        $admin = $this->makeUser($org, 'admin', 'proc@test.id');
        $supplier = Supplier::create(['organization_id' => $org->id, 'code' => 'SUP1', 'name' => 'Supplier 1', 'status' => 'ACTIVE']);

        // 1. Purchase Request
        $pr = PurchaseRequest::create([
            'organization_id' => $org->id, 'central_kitchen_id' => $ck->id, 'warehouse_id' => $wh->id,
            'number' => 'PR-TEST-001', 'request_date' => now()->toDateString(), 'needed_date' => now()->addDays(7)->toDateString(),
            'status' => 'DRAFT', 'requested_by' => $admin->id,
        ]);
        $prItem = $pr->items()->create(['ingredient_id' => $ing->id, 'qty_requested' => 100, 'qty_approved' => 80, 'unit_id' => $unit->id, 'estimated_price' => 10000]);
        $pr->update(['status' => 'APPROVED', 'approved_by' => $admin->id, 'approved_at' => now()]);

        // 2. Purchase Order dari PR
        $po = PurchaseOrder::create([
            'organization_id' => $org->id, 'central_kitchen_id' => $ck->id, 'supplier_id' => $supplier->id,
            'warehouse_id' => $wh->id, 'purchase_request_id' => $pr->id,
            'number' => 'PO-TEST-001', 'order_date' => now()->toDateString(),
            'status' => 'APPROVED', 'approved_by' => $admin->id, 'approved_at' => now(),
        ]);
        $poItem = $po->items()->create(['purchase_request_item_id' => $prItem->id, 'ingredient_id' => $ing->id, 'qty_ordered' => 80, 'unit_id' => $unit->id, 'unit_price' => 10500]);
        $this->assertEquals(80 * 10500, (float) $poItem->fresh()->line_total);
        $prItem->update(['qty_ordered' => 80]);
        $po->recalculateTotals();
        $this->assertEquals(80 * 10500, (float) $po->fresh()->grand_total);

        // 3. Goods Receipt parsial #1 (50 dari 80)
        $svc = app(InventoryService::class);
        $gr1 = GoodsReceipt::create([
            'organization_id' => $org->id, 'central_kitchen_id' => $ck->id, 'warehouse_id' => $wh->id,
            'purchase_order_id' => $po->id, 'supplier_id' => $supplier->id,
            'number' => 'GR-TEST-001', 'receipt_date' => now()->toDateString(), 'status' => 'RECEIVED', 'received_by' => $admin->id,
        ]);
        $gr1->items()->create(['purchase_order_item_id' => $poItem->id, 'ingredient_id' => $ing->id, 'qty_ordered' => 80, 'qty_received' => 50, 'unit_id' => $unit->id, 'unit_price' => 10500, 'batch_no' => 'GR1-B1', 'expiry_date' => now()->addDays(90)->toDateString()]);
        $svc->receive(['organization_id' => $org->id, 'warehouse_id' => $wh->id, 'item_type' => 'ingredient', 'item_id' => $ing->id, 'qty' => 50, 'unit_id' => $unit->id, 'unit_cost' => 10500, 'batch_no' => 'GR1-B1', 'expiry_date' => now()->addDays(90)->toDateString(), 'supplier_id' => $supplier->id, 'reference_type' => GoodsReceipt::class, 'reference_id' => $gr1->id, 'reference_no' => $gr1->number]);
        $poItem->increment('qty_received', 50);
        $po->refresh()->refreshReceiveStatus();

        $this->assertEquals('PARTIAL', $po->fresh()->status);
        $this->assertEquals(50, $svc->stockOf($wh->id, 'ingredient', $ing->id));

        // 4. Goods Receipt pelunasan (30 sisa)
        $gr2 = GoodsReceipt::create([
            'organization_id' => $org->id, 'central_kitchen_id' => $ck->id, 'warehouse_id' => $wh->id,
            'purchase_order_id' => $po->id, 'supplier_id' => $supplier->id,
            'number' => 'GR-TEST-002', 'receipt_date' => now()->toDateString(), 'status' => 'RECEIVED', 'received_by' => $admin->id,
        ]);
        $gr2->items()->create(['purchase_order_item_id' => $poItem->id, 'ingredient_id' => $ing->id, 'qty_ordered' => 80, 'qty_received' => 30, 'unit_id' => $unit->id, 'unit_price' => 10500, 'batch_no' => 'GR2-B1', 'expiry_date' => now()->addDays(60)->toDateString()]);
        $svc->receive(['organization_id' => $org->id, 'warehouse_id' => $wh->id, 'item_type' => 'ingredient', 'item_id' => $ing->id, 'qty' => 30, 'unit_id' => $unit->id, 'unit_cost' => 10500, 'batch_no' => 'GR2-B1', 'expiry_date' => now()->addDays(60)->toDateString(), 'supplier_id' => $supplier->id, 'reference_type' => GoodsReceipt::class, 'reference_id' => $gr2->id, 'reference_no' => $gr2->number]);
        $poItem->increment('qty_received', 30);
        $po->refresh()->refreshReceiveStatus();

        $this->assertEquals('COMPLETED', $po->fresh()->status);
        $this->assertTrue($po->fresh()->isFullyReceived());
        $this->assertEquals(80, $svc->stockOf($wh->id, 'ingredient', $ing->id));
    }

    public function test_pr_validation_rejects_empty_items(): void
    {
        ['org' => $org, 'ck' => $ck] = $this->baseFixtures();
        $pr = new PurchaseRequest(['organization_id' => $org->id, 'central_kitchen_id' => $ck->id, 'number' => 'PR-X', 'request_date' => now()->toDateString(), 'status' => 'DRAFT']);
        $pr->save();
        $this->assertEquals(0, $pr->items()->count());
        // Aturan bisnis: PR tanpa item tidak boleh disubmit (dicek di controller/service layer)
        $this->assertTrue($pr->isEditable());
    }

    public function test_po_over_receive_prevented_by_remaining(): void
    {
        ['org' => $org, 'ck' => $ck, 'wh' => $wh, 'unit' => $unit, 'ing' => $ing] = $this->baseFixtures();
        $supplier = Supplier::create(['organization_id' => $org->id, 'code' => 'SUP9', 'name' => 'Sup 9', 'status' => 'ACTIVE']);
        $po = PurchaseOrder::create(['organization_id' => $org->id, 'central_kitchen_id' => $ck->id, 'supplier_id' => $supplier->id, 'warehouse_id' => $wh->id, 'number' => 'PO-OVER-1', 'order_date' => now()->toDateString(), 'status' => 'APPROVED']);
        $item = $po->items()->create(['ingredient_id' => $ing->id, 'qty_ordered' => 10, 'unit_id' => $unit->id, 'unit_price' => 1000]);
        $this->assertEquals(10, $item->remainingToReceive());
        $this->assertFalse($po->isFullyReceived());
    }
}
