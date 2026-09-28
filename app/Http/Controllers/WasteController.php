<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesOrgAccess;
use App\Http\Controllers\Concerns\FiltersRequests;
use App\Models\Ingredient;
use App\Models\Warehouse;
use App\Models\Waste;
use App\Services\InventoryService;
use App\Services\NumberService;
use App\Services\PeriodService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WasteController extends Controller
{
    use AuthorizesOrgAccess, FiltersRequests;

    public function index(Request $request)
    {
        $query = Waste::where('organization_id', $request->user()->organization_id);
        if ($request->filled('reason')) {
            $query->where('reason', $request->reason);
        }
        $wastes = $this->tableQuery($request, $query, ['number']);

        return view('wastes.index', compact('wastes'));
    }

    public function create(Request $request)
    {
        $warehouses = Warehouse::when($request->user()->central_kitchen_id, fn ($q) => $q->where('central_kitchen_id', $request->user()->central_kitchen_id))->get();
        $ingredients = Ingredient::active()->where('organization_id', $request->user()->organization_id)->with('unit')->get();

        return view('wastes.form', ['waste' => new Waste, 'warehouses' => $warehouses, 'ingredients' => $ingredients]);
    }

    public function store(Request $request, NumberService $numbers, InventoryService $inventory, PeriodService $periods)
    {
        $data = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'ingredient_id' => 'required|exists:ingredients,id',
            'qty' => 'required|numeric|min:0.001',
            'reason' => 'required|in:EXPIRED,SPOILED,OVER_PRODUCTION,QC_REJECT,OTHER',
            'disposal_method' => 'nullable|string|max:40',
            'notes' => 'nullable|string',
        ]);
        $this->ensureWarehouse((int) $data['warehouse_id']);
        $this->ensureOrgAccess(Ingredient::findOrFail($data['ingredient_id']));
        $periods->assertOpen($request->user()->organization_id, Warehouse::find($data['warehouse_id'])->central_kitchen_id, now()->toDateString());
        try {
            $waste = DB::transaction(function () use ($request, $data, $numbers, $inventory) {
                $ing = Ingredient::find($data['ingredient_id']);
                $waste = Waste::create([
                    'organization_id' => $request->user()->organization_id,
                    'central_kitchen_id' => Warehouse::find($data['warehouse_id'])->central_kitchen_id,
                    'warehouse_id' => $data['warehouse_id'],
                    'number' => $numbers->next('WST'),
                    'waste_date' => now()->toDateString(),
                    'item_type' => 'ingredient',
                    'item_id' => $ing->id,
                    'qty' => $data['qty'],
                    'unit_id' => $ing->unit_id,
                    'reason' => $data['reason'],
                    'disposal_method' => $data['disposal_method'] ?? null,
                    'notes' => $data['notes'] ?? null,
                    'reported_by' => $request->user()->id,
                ]);
                $allocs = $inventory->consume($data['warehouse_id'], 'ingredient', $ing->id, (float) $data['qty'], [
                    'organization_id' => $waste->organization_id,
                    'movement_type' => 'WASTE',
                    'unit_id' => $ing->unit_id,
                    'reference_type' => Waste::class,
                    'reference_id' => $waste->id,
                    'reference_no' => $waste->number,
                ]);
                $waste->update(['cost_loss' => array_sum(array_map(fn ($a) => $a['qty'] * $a['unit_cost'], $allocs))]);

                return $waste;
            });
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('wastes.index')->with('success', 'Waste '.$waste->number.' tercatat, stok berkurang.');
    }
}
