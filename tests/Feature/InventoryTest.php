<?php

namespace Tests\Feature;

use App\Models\InventoryMovement;
use App\Models\InventoryStock;
use App\Services\Exceptions\InsufficientStockException;
use App\Services\InventoryService;
use Tests\TestCase;

class InventoryTest extends TestCase
{
    public function test_receive_creates_batch_stock_and_ledger(): void
    {
        ['org' => $org, 'wh' => $wh, 'unit' => $unit, 'ing' => $ing] = $this->baseFixtures();
        $svc = app(InventoryService::class);

        $batch = $svc->receive([
            'organization_id' => $org->id, 'warehouse_id' => $wh->id,
            'item_type' => 'ingredient', 'item_id' => $ing->id,
            'qty' => 100, 'unit_id' => $unit->id, 'unit_cost' => 12000,
            'batch_no' => 'B-001', 'expiry_date' => now()->addDays(60)->toDateString(),
            'reference_type' => 'TEST', 'reference_id' => 1, 'reference_no' => 'T-1',
        ]);

        $this->assertEquals(100, (float) $batch->remaining_qty);
        $this->assertEquals(100, $svc->stockOf($wh->id, 'ingredient', $ing->id));
        $mov = InventoryMovement::where('movement_type', 'PURCHASE_RECEIPT')->first();
        $this->assertNotNull($mov);
        $this->assertEquals(0, (float) $mov->stock_before);
        $this->assertEquals(100, (float) $mov->stock_after);
        $this->assertEquals(12000 * 100, (float) $mov->total_cost);
    }

    public function test_consume_uses_fefo_order(): void
    {
        ['org' => $org, 'wh' => $wh, 'unit' => $unit, 'ing' => $ing] = $this->baseFixtures();
        $svc = app(InventoryService::class);

        // Batch A expired duluan (30 hari), batch B 90 hari. FEFO harus ambil A dulu.
        $svc->receive(['organization_id' => $org->id, 'warehouse_id' => $wh->id, 'item_type' => 'ingredient', 'item_id' => $ing->id, 'qty' => 50, 'unit_id' => $unit->id, 'unit_cost' => 10000, 'batch_no' => 'B-LATE', 'expiry_date' => now()->addDays(90)->toDateString(), 'reference_type' => 'T', 'reference_id' => 11]);
        $batchA = $svc->receive(['organization_id' => $org->id, 'warehouse_id' => $wh->id, 'item_type' => 'ingredient', 'item_id' => $ing->id, 'qty' => 50, 'unit_id' => $unit->id, 'unit_cost' => 11000, 'batch_no' => 'B-SOON', 'expiry_date' => now()->addDays(30)->toDateString(), 'reference_type' => 'T', 'reference_id' => 12]);

        $allocs = $svc->consume($wh->id, 'ingredient', $ing->id, 60, [
            'organization_id' => $org->id, 'movement_type' => 'PRODUCTION_CONSUMPTION',
            'reference_type' => 'WO', 'reference_id' => 1,
        ]);

        $this->assertCount(2, $allocs);
        $this->assertEquals($batchA->id, $allocs[0]['batch_id']); // FEFO: batch A dulu
        $this->assertEquals(50, (float) $allocs[0]['qty']);
        $this->assertEquals(10, (float) $allocs[1]['qty']);
        $this->assertEquals(40, $svc->stockOf($wh->id, 'ingredient', $ing->id));
        $this->assertEquals('DEPLETED', $batchA->fresh()->status);
    }

    public function test_consume_insufficient_stock_throws_and_rolls_back(): void
    {
        ['org' => $org, 'wh' => $wh, 'unit' => $unit, 'ing' => $ing] = $this->baseFixtures();
        $svc = app(InventoryService::class);
        $svc->receive(['organization_id' => $org->id, 'warehouse_id' => $wh->id, 'item_type' => 'ingredient', 'item_id' => $ing->id, 'qty' => 10, 'unit_id' => $unit->id, 'unit_cost' => 5000, 'batch_no' => 'B-1', 'reference_type' => 'T', 'reference_id' => 21]);

        $this->expectException(InsufficientStockException::class);
        try {
            $svc->consume($wh->id, 'ingredient', $ing->id, 999, ['movement_type' => 'WASTE', 'reference_type' => 'W', 'reference_id' => 1]);
        } finally {
            // rollback: stok tetap 10, tidak ada movement WASTE
            $this->assertEquals(10, $svc->stockOf($wh->id, 'ingredient', $ing->id));
            $this->assertEquals(0, InventoryMovement::where('movement_type', 'WASTE')->count());
        }
    }

    public function test_duplicate_posting_rejected(): void
    {
        ['org' => $org, 'wh' => $wh, 'unit' => $unit, 'ing' => $ing] = $this->baseFixtures();
        $svc = app(InventoryService::class);
        $data = ['organization_id' => $org->id, 'warehouse_id' => $wh->id, 'item_type' => 'ingredient', 'item_id' => $ing->id, 'qty' => 5, 'unit_id' => $unit->id, 'unit_cost' => 5000, 'batch_no' => 'B-DUP', 'reference_type' => 'GR', 'reference_id' => 7, 'reference_no' => 'GR-1'];
        $svc->receive($data);

        $this->expectException(\RuntimeException::class);
        $svc->receive($data + ['batch_no' => 'B-DUP-2']);
    }

    public function test_validation_zero_qty_rejected(): void
    {
        ['org' => $org, 'wh' => $wh, 'ing' => $ing] = $this->baseFixtures();
        $svc = app(InventoryService::class);

        $this->expectException(\InvalidArgumentException::class);
        $svc->receive(['organization_id' => $org->id, 'warehouse_id' => $wh->id, 'item_type' => 'ingredient', 'item_id' => $ing->id, 'qty' => 0, 'unit_cost' => 1]);
    }

    public function test_transfer_moves_stock_between_warehouses(): void
    {
        ['org' => $org, 'ck' => $ck, 'wh' => $wh, 'unit' => $unit, 'ing' => $ing] = $this->baseFixtures();
        $wh2 = $this->makeWarehouse($ck, 'WH2');
        $svc = app(InventoryService::class);
        $svc->receive(['organization_id' => $org->id, 'warehouse_id' => $wh->id, 'item_type' => 'ingredient', 'item_id' => $ing->id, 'qty' => 20, 'unit_id' => $unit->id, 'unit_cost' => 8000, 'batch_no' => 'B-T', 'reference_type' => 'T', 'reference_id' => 31]);

        $svc->transfer($wh->id, $wh2->id, 'ingredient', $ing->id, 8, ['organization_id' => $org->id, 'reference_type' => 'TR', 'reference_id' => 1]);

        $this->assertEquals(12, $svc->stockOf($wh->id, 'ingredient', $ing->id));
        $this->assertEquals(8, $svc->stockOf($wh2->id, 'ingredient', $ing->id));
    }

    public function test_reservation_blocks_consume(): void
    {
        ['org' => $org, 'wh' => $wh, 'unit' => $unit, 'ing' => $ing] = $this->baseFixtures();
        $svc = app(InventoryService::class);
        $svc->receive(['organization_id' => $org->id, 'warehouse_id' => $wh->id, 'item_type' => 'ingredient', 'item_id' => $ing->id, 'qty' => 10, 'unit_id' => $unit->id, 'unit_cost' => 5000, 'batch_no' => 'B-R', 'reference_type' => 'T', 'reference_id' => 41]);

        $svc->reserve($wh->id, 'ingredient', $ing->id, 10);
        $this->assertEquals(0, $svc->availableOf($wh->id, 'ingredient', $ing->id));

        $this->expectException(InsufficientStockException::class);
        $svc->consume($wh->id, 'ingredient', $ing->id, 1, ['movement_type' => 'STOCK_OUT', 'reference_type' => 'S', 'reference_id' => 9]);
    }

    public function test_opname_posting_adjusts_to_physical(): void
    {
        ['org' => $org, 'wh' => $wh, 'unit' => $unit, 'ing' => $ing] = $this->baseFixtures();
        $svc = app(InventoryService::class);
        $batch = $svc->receive(['organization_id' => $org->id, 'warehouse_id' => $wh->id, 'item_type' => 'ingredient', 'item_id' => $ing->id, 'qty' => 100, 'unit_id' => $unit->id, 'unit_cost' => 5000, 'batch_no' => 'B-O', 'reference_type' => 'T', 'reference_id' => 51]);

        $mov = $svc->postOpnameItem($wh->id, 'ingredient', $ing->id, $batch->id, 100, 97, ['organization_id' => $org->id, 'reference_type' => 'OPN', 'reference_id' => 1]);

        $this->assertNotNull($mov);
        $this->assertEquals('OUT', $mov->direction);
        $this->assertEquals(3, (float) $mov->qty);
        $this->assertEquals(97, $svc->stockOf($wh->id, 'ingredient', $ing->id));
    }

    public function test_stock_consistency_ledger_sum_equals_stock(): void
    {
        ['org' => $org, 'wh' => $wh, 'unit' => $unit, 'ing' => $ing] = $this->baseFixtures();
        $svc = app(InventoryService::class);
        $svc->receive(['organization_id' => $org->id, 'warehouse_id' => $wh->id, 'item_type' => 'ingredient', 'item_id' => $ing->id, 'qty' => 100, 'unit_id' => $unit->id, 'unit_cost' => 5000, 'batch_no' => 'B-C1', 'reference_type' => 'T', 'reference_id' => 61]);
        $svc->receive(['organization_id' => $org->id, 'warehouse_id' => $wh->id, 'item_type' => 'ingredient', 'item_id' => $ing->id, 'qty' => 50, 'unit_id' => $unit->id, 'unit_cost' => 6000, 'batch_no' => 'B-C2', 'reference_type' => 'T', 'reference_id' => 62]);
        $svc->consume($wh->id, 'ingredient', $ing->id, 70, ['movement_type' => 'PRODUCTION_CONSUMPTION', 'reference_type' => 'WO', 'reference_id' => 61]);

        $in = (float) InventoryMovement::where('warehouse_id', $wh->id)->where('item_type', 'ingredient')->where('item_id', $ing->id)->where('direction', 'IN')->sum('qty');
        $out = (float) InventoryMovement::where('warehouse_id', $wh->id)->where('item_type', 'ingredient')->where('item_id', $ing->id)->where('direction', 'OUT')->sum('qty');
        $this->assertEquals($in - $out, $svc->stockOf($wh->id, 'ingredient', $ing->id));
        $this->assertEquals(80, $svc->stockOf($wh->id, 'ingredient', $ing->id));
        $this->assertEquals(
            InventoryStock::where('warehouse_id', $wh->id)->where('item_type', 'ingredient')->where('item_id', $ing->id)->sum('qty'),
            $svc->stockOf($wh->id, 'ingredient', $ing->id)
        );
    }
}
