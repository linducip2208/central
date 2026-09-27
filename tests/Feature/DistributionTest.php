<?php

namespace Tests\Feature;

use App\Models\Delivery;
use App\Models\Distribution;
use App\Models\Packaging;
use App\Models\School;
use App\Models\Waste;
use App\Services\InventoryService;
use Tests\TestCase;

class DistributionTest extends TestCase
{
    public function test_packaging_distribution_delivery_workflow(): void
    {
        ['org' => $org, 'ck' => $ck, 'wh' => $wh, 'unit' => $unit, 'prd' => $prd] = $this->baseFixtures();
        $admin = $this->makeUser($org, 'admin', 'dist@test.id');
        $svc = app(InventoryService::class);

        $school = School::create(['organization_id' => $org->id, 'central_kitchen_id' => $ck->id, 'code' => 'SCH1', 'name' => 'SDN 1', 'level' => 'SD', 'student_count' => 300, 'target_portions' => 300, 'status' => 'ACTIVE']);

        // Stok produk jadi 500
        $svc->produceOutput(['organization_id' => $org->id, 'warehouse_id' => $wh->id, 'item_id' => $prd->id, 'qty' => 500, 'unit_id' => $unit->id, 'unit_cost' => 15000, 'batch_no' => 'PRD-D1', 'reference_type' => 'T', 'reference_id' => 81]);

        // Packaging 300 box
        $pkg = Packaging::create(['organization_id' => $org->id, 'central_kitchen_id' => $ck->id, 'warehouse_id' => $wh->id, 'number' => 'PKG-1', 'packaging_date' => now()->toDateString(), 'packages_planned' => 300, 'packages_done' => 300, 'package_type' => 'BOX', 'status' => 'COMPLETED', 'created_by' => $admin->id]);
        $pkg->items()->create(['product_id' => $prd->id, 'qty_packed' => 300]);

        // Distribusi ke sekolah
        $dist = Distribution::create(['organization_id' => $org->id, 'central_kitchen_id' => $ck->id, 'packaging_id' => $pkg->id, 'number' => 'DST-1', 'distribution_date' => now()->toDateString(), 'total_portions' => 300, 'status' => 'IN_TRANSIT', 'created_by' => $admin->id]);
        $dist->items()->create(['school_id' => $school->id, 'product_id' => $prd->id, 'qty_planned' => 300]);

        // Delivery: keluar stok 300 (FEFO), partial 290 terkirim + 10 return
        $allocs = $svc->consume($wh->id, 'product', $prd->id, 300, ['organization_id' => $org->id, 'movement_type' => 'DELIVERY', 'reference_type' => Delivery::class, 'reference_id' => 999, 'reference_no' => 'DLV-X']);
        $this->assertNotEmpty($allocs);

        $dlv = Delivery::create(['organization_id' => $org->id, 'central_kitchen_id' => $ck->id, 'distribution_id' => $dist->id, 'school_id' => $school->id, 'number' => 'DLV-1', 'delivery_date' => now()->toDateString(), 'qty_planned' => 300, 'qty_delivered' => 290, 'qty_returned' => 10, 'status' => 'PARTIAL', 'delivered_at' => now(), 'received_by_name' => 'Pak Guru']);
        $dlv->items()->create(['product_id' => $prd->id, 'qty_planned' => 300, 'qty_delivered' => 290, 'qty_returned' => 10]);
        $dlv->trackings()->create(['status' => 'IN_TRANSIT', 'notes' => 'Berangkat']);
        $dlv->trackings()->create(['status' => 'DELIVERED', 'notes' => 'Diterima sebagian']);

        $this->assertEquals(200, $svc->stockOf($wh->id, 'product', $prd->id));
        $this->assertEquals(2, $dlv->trackings()->count());
        $this->assertEquals('PARTIAL', $dlv->status);

        // Return 10 kembali ke stok
        $svc->receive(['organization_id' => $org->id, 'warehouse_id' => $wh->id, 'item_type' => 'product', 'item_id' => $prd->id, 'qty' => 10, 'unit_id' => $unit->id, 'unit_cost' => 15000, 'batch_no' => 'PRD-RET1', 'reference_type' => Delivery::class, 'reference_id' => $dlv->id, 'reference_no' => $dlv->number, 'notes' => 'Retur baik']);
        $this->assertEquals(210, $svc->stockOf($wh->id, 'product', $prd->id));
    }

    public function test_waste_reduces_stock_and_records_loss(): void
    {
        ['org' => $org, 'ck' => $ck, 'wh' => $wh, 'unit' => $unit, 'ing' => $ing] = $this->baseFixtures();
        $admin = $this->makeUser($org, 'admin', 'waste@test.id');
        $svc = app(InventoryService::class);
        $svc->receive(['organization_id' => $org->id, 'warehouse_id' => $wh->id, 'item_type' => 'ingredient', 'item_id' => $ing->id, 'qty' => 50, 'unit_id' => $unit->id, 'unit_cost' => 8000, 'batch_no' => 'W-B1', 'reference_type' => 'T', 'reference_id' => 91]);

        $waste = Waste::create(['organization_id' => $org->id, 'central_kitchen_id' => $ck->id, 'warehouse_id' => $wh->id, 'number' => 'WST-1', 'waste_date' => now()->toDateString(), 'item_type' => 'ingredient', 'item_id' => $ing->id, 'qty' => 5, 'unit_id' => $unit->id, 'cost_loss' => 40000, 'reason' => 'SPOILED', 'reported_by' => $admin->id]);
        $svc->consume($wh->id, 'ingredient', $ing->id, 5, ['organization_id' => $org->id, 'movement_type' => 'WASTE', 'reference_type' => Waste::class, 'reference_id' => $waste->id, 'reference_no' => $waste->number]);

        $this->assertEquals(45, $svc->stockOf($wh->id, 'ingredient', $ing->id));
    }

    public function test_authorization_guest_cannot_access_dashboard(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }
}
