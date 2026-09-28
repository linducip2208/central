<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesOrgAccess;
use App\Http\Controllers\Concerns\FiltersRequests;
use App\Models\DemandPlan;
use App\Models\MrpRun;
use App\Models\PurchaseRequest;
use App\Models\Warehouse;
use App\Services\MrpService;
use App\Services\NumberService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MrpController extends Controller
{
    use AuthorizesOrgAccess, FiltersRequests;

    public function index(Request $request)
    {
        $query = MrpRun::with(['warehouse'])->where('organization_id', $request->user()->organization_id);
        $this->scopeKitchen($request, $query);
        $runs = $this->tableQuery($request, $query, ['number']);
        $plans = DemandPlan::where('organization_id', $request->user()->organization_id)->where('status', 'APPROVED')->latest()->take(20)->get();
        $warehouses = Warehouse::when($request->user()->central_kitchen_id, fn ($q) => $q->where('central_kitchen_id', $request->user()->central_kitchen_id))->get();

        return view('mrp.index', compact('runs', 'plans', 'warehouses'));
    }

    public function run(Request $request, MrpService $mrp)
    {
        $data = $request->validate([
            'demand_plan_id' => 'required|exists:demand_plans,id',
            'warehouse_id' => 'required|exists:warehouses,id',
        ]);
        $plan = DemandPlan::findOrFail($data['demand_plan_id']);
        $this->ensureOrgAccess($plan);
        $this->ensureWarehouse((int) $data['warehouse_id']);
        abort_unless($plan->status === 'APPROVED', 422, 'Demand plan harus APPROVED.');

        try {
            $run = $mrp->run($plan, (int) $data['warehouse_id'], $request->user()->id);
        } catch (\Throwable $e) {
            return back()->with('error', 'MRP gagal: '.$e->getMessage());
        }

        return redirect()->route('mrp.show', $run)->with('success', 'MRP selesai: '.$run->lines()->count().' bahan dihitung.');
    }

    public function show(MrpRun $run)
    {
        $this->ensureOrgAccess($run);
        $run->load(['lines.ingredient.unit', 'lines.suggestedSupplier', 'warehouse']);
        $lines = $run->lines()->with(['ingredient.unit', 'suggestedSupplier'])->orderByDesc('net_requirement')->paginate(30);

        return view('mrp.show', compact('run', 'lines'));
    }

    /** Konversi garis PURCHASE menjadi draft PR. */
    public function toPr(Request $request, MrpRun $run, NumberService $numbers)
    {
        $this->ensureOrgAccess($run);
        $data = $request->validate(['line_ids' => 'required|array|min:1', 'line_ids.*' => 'exists:mrp_lines,id']);
        $pr = DB::transaction(function () use ($request, $run, $data, $numbers) {
            $pr = PurchaseRequest::create([
                'organization_id' => $run->organization_id,
                'central_kitchen_id' => $run->central_kitchen_id,
                'warehouse_id' => $run->warehouse_id,
                'number' => $numbers->next('PR'),
                'request_date' => now()->toDateString(),
                'status' => 'DRAFT',
                'notes' => 'Dari MRP '.$run->number,
                'requested_by' => $request->user()->id,
            ]);
            foreach ($run->lines()->whereIn('id', $data['line_ids'])->where('recommendation', 'PURCHASE')->get() as $line) {
                $pr->items()->create([
                    'ingredient_id' => $line->ingredient_id,
                    'qty_requested' => max(0.001, (float) $line->suggested_order_qty),
                    'unit_id' => $line->ingredient->unit_id,
                    'estimated_price' => (float) $line->suggested_price,
                ]);
            }
            abort_unless($pr->items()->exists(), 422, 'Tidak ada garis PURCHASE yang dipilih.');

            return $pr;
        });

        return redirect()->route('purchase-requests.show', $pr)->with('success', 'Draft PR '.$pr->number.' dibuat dari MRP.');
    }
}
