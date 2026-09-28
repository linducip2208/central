<?php

namespace Tests\Feature;

use App\Models\Batch;
use App\Models\Delivery;
use App\Models\GoodsReceipt;
use App\Models\ProductionOrder;
use App\Models\PurchaseOrder;
use App\Models\Recall;
use App\Models\School;
use App\Models\Supplier;
use App\Services\Exceptions\InsufficientStockException;
use App\Services\InventoryService;
use App\Services\TraceabilityService;
use Tests\TestCase;

class TraceabilityTest extends TestCase
{
    protected function seedChain(): array
    {
        ['org' => $org, 'ck' => $ck, 'wh' => $wh, 'unit' => $unit, 'ing' => $ing, 'prd' => $prd] = $this->baseFixtures();
        $admin = $this->makeUser($org, 'admin', 'trace@mbg.id');
        $svc = app(InventoryService::class);

        // GR bahan.
        $supplier = Supplier::create(['organization_id' => $org->id, 'code' => 'SUP-TR', 'name' => 'TR', 'status' => 'ACTIVE']);
        $po = PurchaseOrder::create(['organization_id' => $org->id, 'central_kitchen_id' => $ck->id, 'supplier_id' => $supplier->id, 'warehouse_id' => $wh->id, 'number' => 'PO-TR-1', 'order_date' => now()->toDateString(), 'status' => 'APPROVED']);
        $poItem = $po->items()->create(['ingredient_id' => $ing->id, 'qty_ordered' => 100, 'unit_id' => $unit->id, 'unit_price' => 10000]);
        $gr = GoodsReceipt::create(['organization_id' => $org->id, 'central_kitchen_id' => $ck->id, 'warehouse_id' => $wh->id, 'purchase_order_id' => $po->id, 'supplier_id' => $supplier->id, 'number' => 'GR-TR-1', 'receipt_date' => now()->toDateString(), 'status' => 'RECEIVED', 'received_by' => $admin->id]);
        $gr->items()->create(['purchase_order_item_id' => $poItem->id, 'ingredient_id' => $ing->id, 'qty_ordered' => 100, 'qty_received' => 100, 'unit_id' => $unit->id, 'unit_price' => 10000, 'batch_no' => 'TR-ING-1']);
        $ingBatch = $svc->receive(['organization_id' => $org->id, 'warehouse_id' => $wh->id, 'item_type' => 'ingredient', 'item_id' => $ing->id, 'qty' => 100, 'unit_id' => $unit->id, 'unit_cost' => 10000, 'batch_no' => 'TR-ING-1', 'supplier_id' => $supplier->id, 'reference_type' => GoodsReceipt::class, 'reference_id' => $gr->id, 'reference_no' => $gr->number]);

        // Produksi: konsumsi + output.
        $order = ProductionOrder::create(['organization_id' => $org->id, 'central_kitchen_id' => $ck->id, 'product_id' => $prd->id, 'number' => 'WO-TR-1', 'production_date' => now()->toDateString(), 'planned_qty' => 50, 'unit_id' => $unit->id, 'status' => 'COMPLETED', 'created_by' => $admin->id]);
        $svc->consume($wh->id, 'ingredient', $ing->id, 20, ['organization_id' => $org->id, 'movement_type' => 'PRODUCTION_CONSUMPTION', 'reference_type' => ProductionOrder::class, 'reference_id' => $order->id, 'reference_no' => $order->number]);
        $prdBatch = $svc->produceOutput(['organization_id' => $org->id, 'warehouse_id' => $wh->id, 'item_id' => $prd->id, 'qty' => 50, 'unit_id' => $unit->id, 'unit_cost' => 15000, 'batch_no' => 'TR-PRD-1', 'reference_type' => ProductionOrder::class, 'reference_id' => $order->id, 'reference_no' => $order->number]);

        // Delivery dengan batch tercatat di item.
        $school = School::create(['organization_id' => $org->id, 'central_kitchen_id' => $ck->id, 'code' => 'SCH-TR', 'name' => 'SD TR', 'level' => 'SD', 'student_count' => 50, 'target_portions' => 50, 'status' => 'ACTIVE']);
        $delivery = Delivery::create(['organization_id' => $org->id, 'central_kitchen_id' => $ck->id, 'school_id' => $school->id, 'number' => 'DLV-TR-1', 'delivery_date' => now()->toDateString(), 'qty_planned' => 50, 'qty_delivered' => 50, 'status' => 'DELIVERED', 'delivered_at' => now()]);
        $delivery->items()->create(['product_id' => $prd->id, 'batch_id' => $prdBatch->id, 'qty_planned' => 50, 'qty_delivered' => 50]);
        $svc->consume($wh->id, 'product', $prd->id, 50, ['organization_id' => $org->id, 'movement_type' => 'DELIVERY', 'reference_type' => Delivery::class, 'reference_id' => $delivery->id, 'reference_no' => $delivery->number]);

        return compact('org', 'admin', 'ingBatch', 'prdBatch', 'order', 'delivery', 'gr');
    }

    public function test_forward_traceability(): void
    {
        ['admin' => $admin, 'ingBatch' => $ingBatch, 'prdBatch' => $prdBatch, 'delivery' => $delivery] = $this->seedChain();
        $chain = app(TraceabilityService::class)->forward($ingBatch);

        $this->assertEquals('GR-TR-1', $chain['goods_receipt']['number']);
        $this->assertCount(1, $chain['productions']);
        $this->assertEquals('WO-TR-1', $chain['productions'][0]['number']);
        $this->assertCount(1, $chain['productions'][0]['finished_batches']);
        $this->assertEquals('DLV-TR-1', $chain['productions'][0]['finished_batches'][0]['deliveries'][0]['number']);
    }

    public function test_backward_traceability(): void
    {
        ['admin' => $admin, 'prdBatch' => $prdBatch] = $this->seedChain();
        $chain = app(TraceabilityService::class)->backward($prdBatch);

        $this->assertEquals('WO-TR-1', $chain['production']['number']);
        $this->assertCount(1, $chain['ingredient_batches']);
        $this->assertEquals('TR-ING-1', $chain['ingredient_batches'][0]['batch_no']);
        $this->assertEquals('GR-TR-1', $chain['ingredient_batches'][0]['goods_receipt']['number']);
    }

    public function test_trace_pages_render(): void
    {
        ['admin' => $admin, 'prdBatch' => $prdBatch] = $this->seedChain();
        $this->actingAs($admin)->get('/trace')->assertOk();
        $this->actingAs($admin)->get("/trace/{$prdBatch->id}")->assertOk()
            ->assertSee('WO-TR-1')->assertSee('DLV-TR-1')->assertSee('TR-ING-1');
    }

    public function test_recall_quarantines_and_reports(): void
    {
        ['org' => $org, 'admin' => $admin, 'ingBatch' => $ingBatch] = $this->seedChain();

        $this->actingAs($admin)->post('/recalls', [
            'trigger_batch_id' => $ingBatch->id, 'reason' => 'CONTAMINATION',
            'severity' => 'CLASS_I', 'description' => 'Dugaan kontaminasi serius pada batch bahan.',
        ])->assertRedirect();
        $recall = Recall::latest()->first();
        // Batch bahan + batch jadi terdampak.
        $this->assertGreaterThanOrEqual(2, $recall->items()->count());

        $this->actingAs($admin)->post("/recalls/{$recall->id}/activate")->assertRedirect();
        $this->assertEquals('BLOCKED', $ingBatch->fresh()->status);
        $this->assertTrue($admin->fresh()->notifications()->where('data->type', 'recall_created')->exists());

        $this->actingAs($admin)->get("/recalls/{$recall->id}")->assertOk()->assertSee('DLV-TR-1');
    }

    public function test_recalled_batch_excluded_from_fefo(): void
    {
        ['admin' => $admin, 'ingBatch' => $ingBatch] = $this->seedChain();
        $ingBatch->update(['status' => 'BLOCKED', 'hold_reason' => 'RECALL TEST']);

        $this->expectException(InsufficientStockException::class);
        app(InventoryService::class)->consume($ingBatch->warehouse_id, 'ingredient', $ingBatch->item_id, 1000, ['movement_type' => 'STOCK_OUT', 'reference_type' => 'T', 'reference_id' => 999]);
    }
}
