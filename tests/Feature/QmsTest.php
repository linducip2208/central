<?php

namespace Tests\Feature;

use App\Models\CapaAction;
use App\Models\InspectionTemplate;
use App\Models\NonConformance;
use App\Models\ProductionOrder;
use App\Models\QualityInspection;
use App\Models\TemperatureLog;
use App\Services\InventoryService;
use Tests\TestCase;

class QmsTest extends TestCase
{
    public function test_inspection_fail_creates_ncr_and_quarantines_batch(): void
    {
        ['org' => $org, 'ck' => $ck, 'wh' => $wh, 'unit' => $unit, 'ing' => $ing, 'prd' => $prd] = $this->baseFixtures();
        $admin = $this->makeUser($org, 'admin', 'qms@mbg.id');
        $order = ProductionOrder::create(['organization_id' => $org->id, 'central_kitchen_id' => $ck->id, 'product_id' => $prd->id, 'number' => 'WO-QMS-1', 'production_date' => now()->toDateString(), 'planned_qty' => 10, 'unit_id' => $unit->id, 'status' => 'COMPLETED']);
        $batch = app(InventoryService::class)->produceOutput(['organization_id' => $org->id, 'warehouse_id' => $wh->id, 'item_id' => $prd->id, 'qty' => 10, 'unit_id' => $unit->id, 'unit_cost' => 15000, 'batch_no' => 'QMS-B1', 'reference_type' => ProductionOrder::class, 'reference_id' => $order->id, 'reference_no' => $order->number]);

        $tpl = InspectionTemplate::create(['organization_id' => $org->id, 'code' => 'QCT-T1', 'name' => 'T1', 'stage' => 'FINISHED', 'parameters' => [['name' => 'Suhu saji', 'spec_min' => 60, 'spec_max' => null, 'unit' => '°C']], 'is_active' => true]);

        // Suhu 40 < 60 → FAILED + notifikasi admin.
        $this->actingAs($admin)->post('/inspections', [
            'inspection_template_id' => $tpl->id, 'reference_kind' => 'production',
            'reference_id' => $order->id, 'measured' => [40],
        ])->assertRedirect();
        $insp = QualityInspection::latest()->first();
        $this->assertEquals('FAILED', $insp->result);
        $this->assertTrue($admin->fresh()->notifications()->where('data->type', 'qc_alert')->exists());

        // NCR dengan disposisi HOLD → batch dikarantina.
        $this->actingAs($admin)->post("/inspections/{$insp->id}/ncr", [
            'batch_id' => $batch->id, 'category' => 'PROCESS', 'severity' => 'MAJOR',
            'description' => 'Suhu saji rendah', 'disposition' => 'HOLD',
        ])->assertRedirect();
        $this->assertEquals('BLOCKED', $batch->fresh()->status);
        $ncr = NonConformance::latest()->first();

        // CAPA selesai semua → NCR CLOSED.
        $this->actingAs($admin)->post("/ncrs/{$ncr->id}/capa", ['action_type' => 'CORRECTIVE', 'action' => 'Panaskan ulang'])->assertRedirect();
        $capa = CapaAction::latest()->first();
        $this->actingAs($admin)->post("/capa/{$capa->id}/complete")->assertRedirect();
        $this->assertEquals('CLOSED', $ncr->fresh()->status);
    }

    public function test_temperature_out_of_spec_warns(): void
    {
        ['org' => $org] = $this->baseFixtures();
        $admin = $this->makeUser($org, 'admin', 'qms2@mbg.id');

        $this->actingAs($admin)->post('/temp-logs', ['checkpoint' => 'COLD_STORAGE', 'temperature_c' => 12])->assertSessionHas('error');
        $log = TemperatureLog::latest()->first();
        $this->assertFalse((bool) $log->in_spec);

        $this->actingAs($admin)->post('/temp-logs', ['checkpoint' => 'COLD_STORAGE', 'temperature_c' => 3])->assertSessionHas('success');
    }
}
