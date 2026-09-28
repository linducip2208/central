<?php

namespace Tests\Feature;

use App\Models\ApprovalDelegation;
use App\Models\ApprovalMatrix;
use App\Models\AutomationRule;
use App\Models\Bom;
use App\Models\Delivery;
use App\Models\DemandForecast;
use App\Models\Document;
use App\Models\GoodsReceipt;
use App\Models\Import;
use App\Models\PurchaseOrder;
use App\Models\School;
use App\Models\Supplier;
use App\Models\SupplierReturn;
use App\Services\AutomationService;
use App\Services\InventoryService;
use App\Services\KpiService;
use Illuminate\Http\UploadedFile;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class EnterpriseTest extends TestCase
{
    public function test_kpi_service_and_control_tower(): void
    {
        ['org' => $org] = $this->baseFixtures();
        $admin = $this->makeUser($org, 'admin', 'kpi@mbg.id');

        $kpi = app(KpiService::class)->summary($org->id, null, now()->subDays(30)->toDateString(), now()->toDateString());
        $this->assertArrayHasKey('service_level', $kpi);
        $this->assertArrayHasKey('cost_per_portion', $kpi);

        $tower = app(KpiService::class)->controlTower($org->id, null);
        $this->assertArrayHasKey('capa_overdue', $tower);

        $this->actingAs($admin)->get('/tms/tower')->assertOk()->assertSee('Perlu perhatian');
        $this->actingAs($admin)->get('/analytics')->assertOk();
    }

    public function test_automation_rule_fires_notification(): void
    {
        ['org' => $org] = $this->baseFixtures();
        $admin = $this->makeUser($org, 'admin', 'auto@mbg.id');
        AutomationRule::create(['organization_id' => $org->id, 'name' => 'R1', 'event' => 'qc.failed', 'action' => 'notify_role', 'target_role' => 'admin', 'message' => 'QC gagal {{number}}', 'is_active' => true]);

        $fired = app(AutomationService::class)->fire('qc.failed', ['organization_id' => $org->id, 'number' => 'Q-1']);
        $this->assertEquals(1, $fired);
        $this->assertTrue($admin->fresh()->notifications()->where('data->type', 'qc.failed')->exists());

        $this->actingAs($admin)->get('/automation')->assertOk()->assertSee('R1');
    }

    public function test_automation_conditions_filter(): void
    {
        ['org' => $org] = $this->baseFixtures();
        AutomationRule::create(['organization_id' => $org->id, 'name' => 'R2', 'event' => 'stock.low', 'conditions' => ['days' => ['max' => 3]], 'action' => 'notify_role', 'target_role' => 'admin', 'is_active' => true]);

        $this->assertEquals(0, app(AutomationService::class)->fire('stock.low', ['organization_id' => $org->id, 'days' => 10]));
        $this->assertEquals(1, app(AutomationService::class)->fire('stock.low', ['organization_id' => $org->id, 'days' => 2]));
    }

    public function test_document_lifecycle_and_ack(): void
    {
        ['org' => $org] = $this->baseFixtures();
        $admin = $this->makeUser($org, 'admin', 'doc@mbg.id');

        $this->actingAs($admin)->post('/documents', ['category' => 'SOP', 'title' => 'SOP Cuci Tangan', 'content' => 'Langkah 1...'])->assertRedirect();
        $doc = Document::latest()->first();
        $this->assertEquals('DRAFT', $doc->status);
        $this->actingAs($admin)->post("/documents/{$doc->id}/submit")->assertRedirect();
        $this->actingAs($admin)->post("/documents/{$doc->id}/approve")->assertRedirect();
        $this->actingAs($admin)->post("/documents/{$doc->id}/publish")->assertRedirect();
        $this->assertTrue($doc->fresh()->isPublished());
        $this->actingAs($admin)->post("/documents/{$doc->id}/ack")->assertRedirect();
        $this->assertEquals(1, $doc->acknowledgements()->count());
        $this->actingAs($admin)->get("/documents/{$doc->id}")->assertOk()->assertSee('SUDAH DIBACA');
    }

    public function test_import_preview_commit(): void
    {
        ['org' => $org, 'ck' => $ck] = $this->baseFixtures();
        $admin = $this->makeUser($org, 'admin', 'imp@mbg.id');
        $school = School::create(['organization_id' => $org->id, 'central_kitchen_id' => $ck->id, 'code' => 'SCH-IMP', 'name' => 'SD IMP', 'level' => 'SD', 'student_count' => 10, 'target_portions' => 10, 'status' => 'ACTIVE']);

        $csv = "school_code,name,identifier,grade,class,gender,allergy\nSCH-IMP,Ani,N1,1,A,P,\nSCH-IMP,Budi,N2,1,A,L,kacang\n";
        $tmp = tempnam(sys_get_temp_dir(), 'imp').'.csv';
        file_put_contents($tmp, $csv);
        $file = new UploadedFile($tmp, 'i.csv', 'text/csv', null, true);

        // Dry-run: belum ada data tersimpan.
        $resp = $this->actingAs($admin)->post('/imports/recipients/preview', ['file' => $file]);
        $resp->assertOk();
        $this->assertEquals(0, $school->recipients()->count());

        // Commit via tmp yang dikembalikan preview.
        $tmp2 = tempnam(sys_get_temp_dir(), 'imp').'.csv';
        file_put_contents($tmp2, $csv);
        $file2 = new UploadedFile($tmp2, 'i.csv', 'text/csv', null, true);
        $preview = $this->actingAs($admin)->post('/imports/recipients/preview', ['file' => $file2]);
        $tmpToken = $preview->viewData('tmp');
        $this->assertNotEmpty($tmpToken);
        $this->actingAs($admin)->post('/imports/recipients/commit', ['tmp' => $tmpToken])->assertRedirect('/imports');
        $this->assertEquals(2, $school->recipients()->count());
        $import = Import::latest()->first();
        $this->assertEquals(2, $import->imported_rows);
        @unlink($tmp);
        @unlink($tmp2);
    }

    public function test_global_search_respects_org(): void
    {
        ['org' => $org] = $this->baseFixtures();
        $admin = $this->makeUser($org, 'admin', 'srch@mbg.id');
        Supplier::create(['organization_id' => $org->id, 'code' => 'SUP-SRC', 'name' => 'UnikSupplierXYZ', 'status' => 'ACTIVE']);

        $this->actingAs($admin)->get('/search?q=UnikSupplierXYZ')->assertOk()->assertSee('UnikSupplierXYZ');
        $this->actingAs($admin)->getJson('/search?q=UnikSupplierXYZ&format=json')->assertOk()->assertJsonPath('q', 'UnikSupplierXYZ');

        $other = $this->makeOrg('ORGQ');
        $outsider = $this->makeUser($other, 'admin', 'srch2@mbg.id');
        $this->actingAs($outsider)->get('/search?q=UnikSupplierXYZ')->assertOk()->assertDontSee('SUP-SRC');
    }

    public function test_supplier_return_reduces_stock(): void
    {
        ['org' => $org, 'ck' => $ck, 'wh' => $wh, 'unit' => $unit, 'ing' => $ing] = $this->baseFixtures();
        $admin = $this->makeUser($org, 'admin', 'srt@mbg.id');
        $supplier = Supplier::create(['organization_id' => $org->id, 'code' => 'SUP-SR', 'name' => 'SR', 'status' => 'ACTIVE']);
        $po = PurchaseOrder::create(['organization_id' => $org->id, 'central_kitchen_id' => $ck->id, 'supplier_id' => $supplier->id, 'warehouse_id' => $wh->id, 'number' => 'PO-SR-1', 'order_date' => now()->toDateString(), 'status' => 'APPROVED']);
        $item = $po->items()->create(['ingredient_id' => $ing->id, 'qty_ordered' => 100, 'unit_id' => $unit->id, 'unit_price' => 1000]);
        $gr = GoodsReceipt::create(['organization_id' => $org->id, 'central_kitchen_id' => $ck->id, 'warehouse_id' => $wh->id, 'purchase_order_id' => $po->id, 'supplier_id' => $supplier->id, 'number' => 'GR-SR-1', 'receipt_date' => now()->toDateString(), 'status' => 'RECEIVED', 'received_by' => $admin->id]);
        $grItem = $gr->items()->create(['purchase_order_item_id' => $item->id, 'ingredient_id' => $ing->id, 'qty_ordered' => 100, 'qty_received' => 100, 'unit_id' => $unit->id, 'unit_price' => 1000, 'batch_no' => 'SR-B1']);
        app(InventoryService::class)->receive(['organization_id' => $org->id, 'warehouse_id' => $wh->id, 'item_type' => 'ingredient', 'item_id' => $ing->id, 'qty' => 100, 'unit_id' => $unit->id, 'unit_cost' => 1000, 'batch_no' => 'SR-B1', 'reference_type' => GoodsReceipt::class, 'reference_id' => $gr->id, 'reference_no' => $gr->number]);

        $this->actingAs($admin)->post("/goods-receipts/{$gr->id}/supplier-return", [
            'goods_receipt_item_id' => $grItem->id, 'qty' => 20, 'reason' => 'busuk sebagian',
        ])->assertRedirect();
        $this->assertEquals(80, app(InventoryService::class)->stockOf($wh->id, 'ingredient', $ing->id));
        $this->assertEquals(1, SupplierReturn::count());

        // Melebihi sisa terima ditolak.
        $this->actingAs($admin)->post("/goods-receipts/{$gr->id}/supplier-return", [
            'goods_receipt_item_id' => $grItem->id, 'qty' => 90, 'reason' => 'x',
        ])->assertStatus(422);
    }

    public function test_bom_clone_compare_usedin(): void
    {
        ['org' => $org, 'unit' => $unit, 'ing' => $ing, 'prd' => $prd] = $this->baseFixtures();
        $admin = $this->makeUser($org, 'admin', 'bomx@mbg.id');
        $bom = Bom::create(['organization_id' => $org->id, 'item_type' => 'product', 'item_id' => $prd->id, 'code' => 'BOM-X1', 'version' => '1.0', 'yield_qty' => 10, 'status' => 'ACTIVE']);
        $bom->items()->create(['component_type' => 'ingredient', 'component_id' => $ing->id, 'qty' => 2, 'unit_id' => $unit->id, 'level' => 1]);

        $this->actingAs($admin)->post("/boms/{$bom->id}/clone")->assertRedirect();
        $clone = Bom::where('code', '!=', 'BOM-X1')->firstOrFail();
        $this->assertEquals('1.1', $clone->version);
        $this->assertEquals('DRAFT', $clone->status);
        $this->assertEquals(1, $clone->items()->count());

        $this->actingAs($admin)->get("/boms/used-in/{$ing->id}")->assertOk()->assertSee('BOM-X1');
        $this->actingAs($admin)->get("/boms/compare?a_id={$bom->id}&b_id={$clone->id}&qty=50")->assertOk();
    }

    public function test_forecast_version_accuracy_scenario(): void
    {
        ['org' => $org, 'ck' => $ck, 'prd' => $prd] = $this->baseFixtures();
        $admin = $this->makeUser($org, 'admin', 'fc@mbg.id');

        $this->actingAs($admin)->post('/forecasts', ['product_id' => $prd->id, 'horizon_days' => 7])->assertRedirect();
        $f = DemandForecast::latest()->first();
        $this->assertEquals(1, $f->version);

        // Delivery dalam periode → aktual + MAPE.
        $school = School::create(['organization_id' => $org->id, 'central_kitchen_id' => $ck->id, 'code' => 'SCH-FC', 'name' => 'SD FC', 'level' => 'SD', 'student_count' => 10, 'target_portions' => 10, 'status' => 'ACTIVE']);
        Delivery::create(['organization_id' => $org->id, 'central_kitchen_id' => $ck->id, 'school_id' => $school->id, 'number' => 'DLV-FC-1', 'delivery_date' => now()->toDateString(), 'qty_planned' => 40, 'qty_delivered' => 40, 'status' => 'DELIVERED'])
            ->items()->create(['product_id' => $prd->id, 'qty_planned' => 40, 'qty_delivered' => 40]);
        $this->actingAs($admin)->post("/forecasts/{$f->id}/actual")->assertRedirect();
        $this->assertEquals(40, (float) $f->fresh()->actual_qty);
        $this->assertNotNull($f->fresh()->error_pct);

        $this->actingAs($admin)->post("/forecasts/{$f->id}/scenario", ['factor' => 1.5, 'name' => 'Lebaran'])->assertRedirect();
        $sc = DemandForecast::where('is_scenario', true)->firstOrFail();
        $this->assertEquals('Lebaran', $sc->scenario_name);

        $this->actingAs($admin)->get('/forecasts')->assertOk();
    }

    public function test_approval_matrix_and_delegation(): void
    {
        ['org' => $org, 'ck' => $ck] = $this->baseFixtures();
        $proc = $this->makeUser($org, 'procurement', 'matproc@mbg.id');
        $proc->assignRole('procurement');
        foreach (['po.view', 'po.approve'] as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
            $proc->givePermissionTo($p);
        }
        ApprovalMatrix::create(['organization_id' => $org->id, 'approvable_type' => 'PurchaseOrder', 'min_amount' => 1000000, 'level' => 2, 'role' => 'admin']);

        $supplier = Supplier::create(['organization_id' => $org->id, 'code' => 'SUP-MX', 'name' => 'MX', 'status' => 'ACTIVE']);
        $po = PurchaseOrder::create(['organization_id' => $org->id, 'central_kitchen_id' => $ck->id, 'supplier_id' => $supplier->id, 'number' => 'PO-MX-1', 'order_date' => now()->toDateString(), 'status' => 'SUBMITTED', 'grand_total' => 5000000]);

        // Procurement tanpa peran admin & tanpa delegasi → 403.
        $this->actingAs($proc)->post("/purchase-orders/{$po->id}/approve")->assertForbidden();

        // Delegasi dari admin → boleh.
        $admin = $this->makeUser($org, 'admin', 'matadm@mbg.id');
        ApprovalDelegation::create(['organization_id' => $org->id, 'delegator_id' => $admin->id, 'delegate_id' => $proc->id, 'start_date' => now()->toDateString(), 'end_date' => now()->addDay()->toDateString(), 'is_active' => true]);
        $this->actingAs($proc)->post("/purchase-orders/{$po->id}/approve")->assertRedirect();
        $this->assertEquals('APPROVED', $po->fresh()->status);

        $this->actingAs($admin)->get('/approvals/matrix')->assertOk();
        $this->actingAs($admin)->get('/approvals/delegations')->assertOk();
    }
}
