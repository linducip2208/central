<?php

namespace Tests\Feature;

use App\Models\DemandPlan;
use App\Models\HygieneCheck;
use App\Models\IngredientSubstitution;
use App\Models\KitchenBudget;
use App\Models\PurchaseOrder;
use App\Models\School;
use App\Models\Supplier;
use App\Services\AiAdvisorInterface;
use App\Services\InventoryService;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class Expansion2Test extends TestCase
{
    public function test_substitution_approval_and_safety_recalc(): void
    {
        ['org' => $org, 'unit' => $unit, 'ing' => $ing] = $this->baseFixtures();
        $admin = $this->makeUser($org, 'admin', 'sub@mbg.id');
        $alt = $this->makeIngredient($org, $unit, 'ING-ALT');

        $this->actingAs($admin)->post("/ingredients/{$ing->id}/substitutions", [
            'substitute_id' => $alt->id, 'ratio' => 1.2, 'notes' => 'setara',
        ])->assertRedirect();
        $sub = IngredientSubstitution::latest()->first();
        $this->assertFalse((bool) $sub->is_approved);

        $this->actingAs($admin)->post("/ingredients/{$ing->id}/substitutions/{$sub->id}/approve")->assertRedirect();
        $this->assertTrue((bool) $sub->fresh()->is_approved);

        // Safety recalc dari histori konsumsi (perlu movement OUT).
        ['wh' => $wh2] = $this->baseFixtures();
        $svc = app(InventoryService::class);
        $svc->receive(['organization_id' => $org->id, 'warehouse_id' => $wh2->id, 'item_type' => 'ingredient', 'item_id' => $ing->id, 'qty' => 300, 'unit_id' => $unit->id, 'unit_cost' => 1000, 'batch_no' => 'SUB-B1', 'reference_type' => 'T', 'reference_id' => 131]);
        $svc->consume($wh2->id, 'ingredient', $ing->id, 60, ['movement_type' => 'PRODUCTION_CONSUMPTION', 'reference_type' => 'T', 'reference_id' => 132]);
        $ing->update(['lead_time_days' => 2]);
        $this->actingAs($admin)->post("/ingredients/{$ing->id}/apply-safety")->assertRedirect();
        $this->assertGreaterThan(0, (float) $ing->fresh()->safety_stock);
        $this->actingAs($admin)->get("/ingredients/{$ing->id}")->assertOk()->assertSee('Alternatif');
    }

    public function test_demand_line_manual_adjust(): void
    {
        ['org' => $org, 'ck' => $ck] = $this->baseFixtures();
        $admin = $this->makeUser($org, 'admin', 'dpl@mbg.id');
        $plan = DemandPlan::create(['organization_id' => $org->id, 'central_kitchen_id' => $ck->id, 'number' => 'DP-ADJ', 'period_type' => 'DAILY', 'period_start' => now()->toDateString(), 'period_end' => now()->toDateString(), 'status' => 'DRAFT', 'created_by' => $admin->id]);
        $line = $plan->lines()->create(['demand_date' => now()->toDateString(), 'gross_demand' => 100, 'source' => 'MANUAL']);
        $this->assertEquals(100, $line->net_demand);

        $this->actingAs($admin)->put("/demand-plans/{$plan->id}/lines/{$line->id}", ['manual_adjustment' => 25, 'safety_stock' => 10])->assertRedirect();
        $line->refresh();
        $this->assertEquals(125, $line->adjusted_demand);
        $this->assertEquals(135, $line->net_demand);
    }

    public function test_recipient_csv_import(): void
    {
        ['org' => $org, 'ck' => $ck] = $this->baseFixtures();
        $admin = $this->makeUser($org, 'admin', 'csv@mbg.id');
        $school = School::create(['organization_id' => $org->id, 'central_kitchen_id' => $ck->id, 'code' => 'SCH-CSV', 'name' => 'SD CSV', 'level' => 'SD', 'student_count' => 10, 'target_portions' => 10, 'status' => 'ACTIVE']);

        $csv = "school_code,name,identifier,grade,class,gender,allergy\nSCH-CSV,Budi,NIS1,3,A,L,susu\nSCH-XXX,Salah,NIS2,3,A,L,\nSCH-CSV,,NIS3,3,A,L,\n";
        $tmp = tempnam(sys_get_temp_dir(), 'rcp').'.csv';
        file_put_contents($tmp, $csv);
        $this->actingAs($admin)->post('/recipients/import', ['file' => new UploadedFile($tmp, 'r.csv', 'text/csv', null, true)])->assertRedirect();
        $this->assertEquals(1, $school->recipients()->count());
        $this->assertEquals('susu', $school->recipients()->first()->allergy_notes);
        @unlink($tmp);

        // Header salah ditolak.
        $tmp2 = tempnam(sys_get_temp_dir(), 'rcp').'.csv';
        file_put_contents($tmp2, "a,b\n1,2\n");
        $this->actingAs($admin)->post('/recipients/import', ['file' => new UploadedFile($tmp2, 'r.csv', 'text/csv', null, true)])->assertSessionHas('error');
        @unlink($tmp2);
    }

    public function test_hygiene_checklist(): void
    {
        ['org' => $org] = $this->baseFixtures();
        $admin = $this->makeUser($org, 'admin', 'hyg@mbg.id');

        // Semua lulus → PASSED.
        $this->actingAs($admin)->post('/hygiene', ['check_type' => 'CLEANING', 'area' => 'Ruang masak', 'passed' => ['0', '1', '2', '3', '4']])->assertRedirect();
        $this->assertEquals('PASSED', HygieneCheck::orderByDesc('id')->first()->result);

        // Satu gagal → FAILED.
        $this->actingAs($admin)->post('/hygiene', ['check_type' => 'CLEANING', 'area' => 'Ruang masak', 'passed' => ['0']])->assertRedirect();
        $this->assertEquals('FAILED', HygieneCheck::orderByDesc('id')->first()->result);
        $this->actingAs($admin)->get('/hygiene')->assertOk()->assertSee('Ruang masak');
    }

    public function test_po_budget_warning_and_waste_pattern(): void
    {
        ['org' => $org, 'ck' => $ck, 'wh' => $wh, 'unit' => $unit, 'ing' => $ing] = $this->baseFixtures();
        $admin = $this->makeUser($org, 'admin', 'bud@mbg.id');
        KitchenBudget::create(['organization_id' => $org->id, 'central_kitchen_id' => $ck->id, 'period' => now()->format('Y-m'), 'amount' => 1000]);
        $supplier = Supplier::create(['organization_id' => $org->id, 'code' => 'SUP-BW', 'name' => 'BW', 'status' => 'ACTIVE']);
        $po = PurchaseOrder::create(['organization_id' => $org->id, 'central_kitchen_id' => $ck->id, 'supplier_id' => $supplier->id, 'warehouse_id' => $wh->id, 'number' => 'PO-BW-1', 'order_date' => now()->toDateString(), 'status' => 'SUBMITTED']);
        $po->items()->create(['ingredient_id' => $ing->id, 'qty_ordered' => 10, 'unit_id' => $unit->id, 'unit_price' => 5000]);
        $po->recalculateTotals();

        $resp = $this->actingAs($admin)->post("/purchase-orders/{$po->id}/approve");
        $resp->assertRedirect();
        $this->assertStringContainsString('PERINGATAN', $resp->getSession()->get('success'));

        // Advisory waste memuat pola hari bila ada data.
        $adv = app(AiAdvisorInterface::class)->wasteAnalysis($ck->id);
        $this->assertEquals('waste_analysis', $adv->topic);
    }
}
