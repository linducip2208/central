<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesOrgAccess;
use App\Http\Controllers\Concerns\FiltersRequests;
use App\Models\Ingredient;
use App\Models\InventoryStock;
use App\Models\Product;
use App\Models\StockOpname;
use App\Models\Warehouse;
use App\Services\InventoryService;
use App\Services\NumberService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockOpnameController extends Controller
{
    use AuthorizesOrgAccess, FiltersRequests;

    public function index(Request $request)
    {
        $query = StockOpname::with(['warehouse'])->where('organization_id', $request->user()->organization_id);
        $this->scopeKitchen($request, $query, 'central_kitchen_id');
        $opnames = $this->tableQuery($request, $query, ['number']);

        return view('stock-opnames.index', compact('opnames'));
    }

    public function create(Request $request)
    {
        $warehouses = Warehouse::when($request->user()->central_kitchen_id, fn ($q) => $q->where('central_kitchen_id', $request->user()->central_kitchen_id))->get();

        return view('stock-opnames.form', ['opname' => new StockOpname, 'warehouses' => $warehouses]);
    }

    public function store(Request $request, NumberService $numbers)
    {
        $data = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'opname_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);
        $warehouse = Warehouse::find($data['warehouse_id']);
        $opname = DB::transaction(function () use ($request, $data, $numbers, $warehouse) {
            $opname = StockOpname::create([
                'organization_id' => $request->user()->organization_id,
                'central_kitchen_id' => $warehouse->central_kitchen_id,
                'warehouse_id' => $warehouse->id,
                'number' => $numbers->next('OPN'),
                'opname_date' => $data['opname_date'],
                'status' => 'DRAFT',
                'notes' => $data['notes'] ?? null,
                'counted_by' => $request->user()->id,
            ]);
            // Snapshot sistem per batch saat ini
            $stocks = InventoryStock::with('batch')->where('warehouse_id', $warehouse->id)->where('qty', '>', 0)->get();
            foreach ($stocks as $s) {
                $opname->items()->create([
                    'item_type' => $s->item_type, 'item_id' => $s->item_id, 'batch_id' => $s->batch_id,
                    'system_qty' => $s->qty, 'physical_qty' => $s->qty, 'unit_cost' => $s->batch?->unit_cost ?? $s->avg_cost,
                ]);
            }

            return $opname;
        });

        return redirect()->route('stock-opnames.show', $opname)->with('success', 'Opname '.$opname->number.' dibuat dengan snapshot sistem.');
    }

    public function show(StockOpname $opname)
    {
        $this->ensureOrgAccess($opname);
        $opname->load(['items' => function ($q) {
            $q->orderBy('item_type')->orderBy('item_id');
        }, 'warehouse']);
        $names = Ingredient::whereIn('id', $opname->items->where('item_type', 'ingredient')->pluck('item_id'))->pluck('name', 'id');
        $pnames = Product::whereIn('id', $opname->items->where('item_type', 'product')->pluck('item_id'))->pluck('name', 'id');

        return view('stock-opnames.show', compact('opname', 'names', 'pnames'));
    }

    public function saveCount(Request $request, StockOpname $opname)
    {
        $this->ensureOrgAccess($opname);
        abort_unless($opname->isEditable(), 422);
        $data = $request->validate(['counts' => 'required|array', 'counts.*' => 'nullable|numeric|min:0']);
        foreach ($opname->items as $item) {
            if (array_key_exists($item->id, $data['counts']) && $data['counts'][$item->id] !== null) {
                $item->update(['physical_qty' => $data['counts'][$item->id]]);
            }
        }
        $opname->update(['status' => 'COUNTED']);

        return back()->with('success', 'Hasil hitung fisik tersimpan.');
    }

    public function approve(Request $request, StockOpname $opname)
    {
        $this->ensureOrgAccess($opname);
        abort_unless($opname->status === 'COUNTED', 422);
        $opname->update(['status' => 'APPROVED', 'approved_by' => $request->user()->id, 'approved_at' => now()]);

        return back()->with('success', 'Opname disetujui. Silakan posting untuk menyesuaikan stok.');
    }

    /** Posting: selisih fisik vs sistem menjadi movement STOCK_OPNAME per item. */
    public function post(Request $request, StockOpname $opname, InventoryService $inventory)
    {
        $this->ensureOrgAccess($opname);
        abort_unless($opname->status === 'APPROVED', 422, 'Opname harus APPROVED sebelum posting.');
        try {
            DB::transaction(function () use ($opname, $inventory) {
                $opname->load('items');
                foreach ($opname->items as $item) {
                    $inventory->postOpnameItem(
                        $opname->warehouse_id, $item->item_type, $item->item_id, $item->batch_id,
                        (float) $item->system_qty, (float) $item->physical_qty,
                        ['organization_id' => $opname->organization_id, 'reference_type' => StockOpname::class, 'reference_id' => $opname->id, 'reference_no' => $opname->number, 'unit_cost' => (float) $item->unit_cost]
                    );
                }
                $opname->update(['status' => 'POSTED', 'posted_at' => now()]);
            });
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('stock-opnames.show', $opname)->with('success', 'Opname diposting. Selisih stok masuk ledger.');
    }
}
