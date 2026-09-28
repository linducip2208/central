<?php

namespace Tests\Feature;

use App\Models\GoodsReceipt;
use App\Models\PurchaseOrder;
use App\Models\Rfq;
use App\Models\Supplier;
use App\Models\SupplierInvoice;
use App\Services\InventoryService;
use Tests\TestCase;

class Procurement2Test extends TestCase
{
    public function test_rfq_award_selects_cheapest_and_creates_po(): void
    {
        ['org' => $org, 'ck' => $ck, 'unit' => $unit, 'ing' => $ing] = $this->baseFixtures();
        $admin = $this->makeUser($org, 'admin', 'rfq@mbg.id');
        $s1 = Supplier::create(['organization_id' => $org->id, 'code' => 'SUP-A', 'name' => 'A', 'status' => 'ACTIVE']);
        $s2 = Supplier::create(['organization_id' => $org->id, 'code' => 'SUP-B', 'name' => 'B', 'status' => 'ACTIVE']);

        // Buat RFQ.
        $this->actingAs($admin)->post('/rfqs', [
            'central_kitchen_id' => $ck->id,
            'supplier_ids' => [$s1->id, $s2->id],
            'items' => [['ingredient_id' => $ing->id, 'qty' => 100]],
        ])->assertRedirect();
        $rfq = Rfq::latest()->first();
        $itemId = $rfq->items()->first()->id;

        // Dua penawaran: B lebih murah.
        $this->actingAs($admin)->post("/rfqs/{$rfq->id}/quotations", [
            'supplier_id' => $s1->id, 'prices' => [$itemId => 12000],
        ])->assertRedirect();
        $this->actingAs($admin)->post("/rfqs/{$rfq->id}/quotations", [
            'supplier_id' => $s2->id, 'prices' => [$itemId => 10500],
        ])->assertRedirect();

        $this->actingAs($admin)->get("/rfqs/{$rfq->id}")->assertOk()->assertSee('10.500');

        $this->actingAs($admin)->post("/rfqs/{$rfq->id}/award")->assertRedirect();
        $po = PurchaseOrder::latest()->first();
        $this->assertEquals($s2->id, $po->supplier_id);
        $this->assertEquals(10500, (float) $po->items()->first()->unit_price);
        $this->assertEquals('AWARDED', $rfq->fresh()->status);
    }

    public function test_quotation_from_uninvited_supplier_rejected(): void
    {
        ['org' => $org, 'ck' => $ck, 'ing' => $ing] = $this->baseFixtures();
        $admin = $this->makeUser($org, 'admin', 'rfq2@mbg.id');
        $invited = Supplier::create(['organization_id' => $org->id, 'code' => 'SUP-I', 'name' => 'I', 'status' => 'ACTIVE']);
        $outsider = Supplier::create(['organization_id' => $org->id, 'code' => 'SUP-O', 'name' => 'O', 'status' => 'ACTIVE']);

        $this->actingAs($admin)->post('/rfqs', [
            'central_kitchen_id' => $ck->id,
            'supplier_ids' => [$invited->id],
            'items' => [['ingredient_id' => $ing->id, 'qty' => 10]],
        ]);
        $rfq = Rfq::latest()->first();
        $itemId = $rfq->items()->first()->id;

        $this->actingAs($admin)->post("/rfqs/{$rfq->id}/quotations", [
            'supplier_id' => $outsider->id, 'prices' => [$itemId => 1000],
        ])->assertStatus(422);
    }

    public function test_invoice_three_way_match_and_duplicate_guard(): void
    {
        ['org' => $org, 'ck' => $ck, 'wh' => $wh, 'unit' => $unit, 'ing' => $ing] = $this->baseFixtures();
        $admin = $this->makeUser($org, 'admin', 'inv@mbg.id');
        $supplier = Supplier::create(['organization_id' => $org->id, 'code' => 'SUP-INV', 'name' => 'Inv', 'status' => 'ACTIVE']);
        $po = PurchaseOrder::create(['organization_id' => $org->id, 'central_kitchen_id' => $ck->id, 'supplier_id' => $supplier->id, 'warehouse_id' => $wh->id, 'number' => 'PO-INV-1', 'order_date' => now()->toDateString(), 'status' => 'APPROVED']);
        $poItem = $po->items()->create(['ingredient_id' => $ing->id, 'qty_ordered' => 100, 'unit_id' => $unit->id, 'unit_price' => 10000]);

        // Terima 100 penuh.
        $gr = GoodsReceipt::create(['organization_id' => $org->id, 'central_kitchen_id' => $ck->id, 'warehouse_id' => $wh->id, 'purchase_order_id' => $po->id, 'supplier_id' => $supplier->id, 'number' => 'GR-INV-1', 'receipt_date' => now()->toDateString(), 'status' => 'RECEIVED', 'received_by' => $admin->id]);
        $gr->items()->create(['purchase_order_item_id' => $poItem->id, 'ingredient_id' => $ing->id, 'qty_ordered' => 100, 'qty_received' => 100, 'unit_id' => $unit->id, 'unit_price' => 10000, 'batch_no' => 'INV-B1']);
        app(InventoryService::class)->receive(['organization_id' => $org->id, 'warehouse_id' => $wh->id, 'item_type' => 'ingredient', 'item_id' => $ing->id, 'qty' => 100, 'unit_id' => $unit->id, 'unit_cost' => 10000, 'batch_no' => 'INV-B1', 'reference_type' => GoodsReceipt::class, 'reference_id' => $gr->id, 'reference_no' => $gr->number]);
        $poItem->update(['qty_received' => 100]);

        // Invoice cocok → MATCHED → verify OK.
        $this->actingAs($admin)->post('/invoices', [
            'supplier_id' => $supplier->id, 'purchase_order_id' => $po->id,
            'supplier_invoice_no' => 'SI-001', 'invoice_date' => now()->toDateString(),
            'items' => [['ingredient_id' => $ing->id, 'qty' => 100, 'price' => 10000]],
        ])->assertRedirect();
        $inv = SupplierInvoice::where('supplier_invoice_no', 'SI-001')->firstOrFail();
        $this->assertEquals('MATCHED', $inv->match_status);
        $this->actingAs($admin)->post("/invoices/{$inv->id}/verify")->assertRedirect();
        $this->assertEquals('VERIFIED', $inv->fresh()->status);

        // Duplikat no. invoice supplier ditolak.
        $this->actingAs($admin)->post('/invoices', [
            'supplier_id' => $supplier->id,
            'supplier_invoice_no' => 'SI-001', 'invoice_date' => now()->toDateString(),
            'items' => [['ingredient_id' => $ing->id, 'qty' => 1, 'price' => 1]],
        ])->assertSessionHas('error');

        // Invoice dengan harga beda → VARIANCE → verify ditolak.
        $this->actingAs($admin)->post('/invoices', [
            'supplier_id' => $supplier->id, 'purchase_order_id' => $po->id,
            'supplier_invoice_no' => 'SI-002', 'invoice_date' => now()->toDateString(),
            'items' => [['ingredient_id' => $ing->id, 'qty' => 100, 'price' => 12000]],
        ])->assertRedirect();
        $inv2 = SupplierInvoice::where('supplier_invoice_no', 'SI-002')->firstOrFail();
        $this->assertEquals('VARIANCE', $inv2->match_status);
        $this->assertEquals(100 * 2000, (float) $inv2->price_variance);
        $this->actingAs($admin)->post("/invoices/{$inv2->id}/verify")->assertStatus(422);
    }
}
