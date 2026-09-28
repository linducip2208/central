<?php

namespace Tests\Feature;

use App\Models\Batch;
use App\Models\WarehouseBin;
use App\Models\WarehouseRack;
use App\Models\WarehouseZone;
use App\Services\InventoryService;
use Tests\TestCase;

class WmsTest extends TestCase
{
    public function test_locations_putaway_and_scan(): void
    {
        ['org' => $org, 'wh' => $wh, 'unit' => $unit, 'ing' => $ing] = $this->baseFixtures();
        $admin = $this->makeUser($org, 'admin', 'wms@mbg.id');

        $this->actingAs($admin)->post('/wms/zones', ['warehouse_id' => $wh->id, 'code' => 'A', 'name' => 'Zona A', 'zone_type' => 'STORAGE'])->assertRedirect();
        $zone = WarehouseZone::where('warehouse_id', $wh->id)->firstOrFail();
        $this->actingAs($admin)->post('/wms/racks', ['warehouse_zone_id' => $zone->id, 'code' => 'R1', 'name' => 'Rak 1'])->assertRedirect();
        $rack = WarehouseRack::where('warehouse_zone_id', $zone->id)->firstOrFail();
        $this->actingAs($admin)->post('/wms/bins', ['warehouse_rack_id' => $rack->id, 'code' => 'B1'])->assertRedirect();
        $bin = WarehouseBin::where('warehouse_rack_id', $rack->id)->firstOrFail();
        $this->assertEquals('A-R1-B1', $bin->barcode);

        $batch = app(InventoryService::class)->receive([
            'organization_id' => $org->id, 'warehouse_id' => $wh->id,
            'item_type' => 'ingredient', 'item_id' => $ing->id,
            'qty' => 10, 'unit_id' => $unit->id, 'unit_cost' => 1000,
            'batch_no' => 'WMS-B1', 'reference_type' => 'T', 'reference_id' => 101,
        ]);
        $this->actingAs($admin)->post("/wms/putaway/{$batch->id}", ['bin_id' => $bin->id])->assertRedirect();
        $this->assertEquals($bin->id, $batch->fresh()->bin_id);

        // Scan barcode bin & nomor batch.
        $this->actingAs($admin)->get('/wms/scan?code=A-R1-B1')->assertOk()->assertSee('WMS-B1');
        $this->actingAs($admin)->get('/wms/scan?code=WMS-B1')->assertOk()->assertSee('A-R1-B1');
        $this->actingAs($admin)->get('/wms/scan?code=UNKNOWN-XYZ')->assertOk()->assertSee('tidak ditemukan');
    }

    public function test_quarantine_blocks_fefo(): void
    {
        ['org' => $org, 'wh' => $wh, 'unit' => $unit, 'ing' => $ing] = $this->baseFixtures();
        $admin = $this->makeUser($org, 'admin', 'wms2@mbg.id');
        $svc = app(InventoryService::class);
        $svc->receive(['organization_id' => $org->id, 'warehouse_id' => $wh->id, 'item_type' => 'ingredient', 'item_id' => $ing->id, 'qty' => 10, 'unit_id' => $unit->id, 'unit_cost' => 1000, 'batch_no' => 'WMS-Q1', 'expiry_date' => now()->addDays(10)->toDateString(), 'reference_type' => 'T', 'reference_id' => 102]);
        $batch2 = $svc->receive(['organization_id' => $org->id, 'warehouse_id' => $wh->id, 'item_type' => 'ingredient', 'item_id' => $ing->id, 'qty' => 10, 'unit_id' => $unit->id, 'unit_cost' => 1000, 'batch_no' => 'WMS-Q2', 'expiry_date' => now()->addDays(90)->toDateString(), 'reference_type' => 'T', 'reference_id' => 103]);

        // Karantina batch yang expired duluan (Q1, FEFO pertama) → konsumsi harus ambil batch kedua.
        $q1 = Batch::where('batch_no', 'WMS-Q1')->firstOrFail();
        $this->actingAs($admin)->post("/wms/quarantine/{$q1->id}", ['hold_reason' => 'dicurigai'])->assertRedirect();

        $allocs = $svc->consume($wh->id, 'ingredient', $ing->id, 5, ['movement_type' => 'STOCK_OUT', 'reference_type' => 'T', 'reference_id' => 104]);
        $this->assertEquals($batch2->id, $allocs[0]['batch_id']);
        $this->assertEquals('BLOCKED', $q1->fresh()->status);

        // Release kembali.
        $this->actingAs($admin)->post("/wms/release/{$q1->id}")->assertRedirect();
        $this->assertEquals('AVAILABLE', $q1->fresh()->status);
    }
}
