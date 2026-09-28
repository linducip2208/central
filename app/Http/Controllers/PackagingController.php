<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesOrgAccess;
use App\Http\Controllers\Concerns\FiltersRequests;
use App\Models\CentralKitchen;
use App\Models\Ingredient;
use App\Models\Packaging;
use App\Models\ProductionOrder;
use App\Models\Warehouse;
use App\Services\InventoryService;
use App\Services\NumberService;
use Illuminate\Http\Request;

class PackagingController extends Controller
{
    use AuthorizesOrgAccess, FiltersRequests;

    public function index(Request $request)
    {
        $query = Packaging::with(['productionOrder'])->where('organization_id', $request->user()->organization_id);
        $this->scopeKitchen($request, $query);
        $pkgs = $this->tableQuery($request, $query, ['number']);

        return view('packagings.index', compact('pkgs'));
    }

    public function create(Request $request)
    {
        $orders = ProductionOrder::where('organization_id', $request->user()->organization_id)->whereIn('status', ['PARTIAL', 'COMPLETED'])->with('product')->latest()->take(30)->get();
        $warehouses = Warehouse::whereHas('centralKitchen', fn ($q) => $q->where('organization_id', $request->user()->organization_id))
            ->when($request->user()->central_kitchen_id, fn ($q) => $q->where('central_kitchen_id', $request->user()->central_kitchen_id))->get();

        return view('packagings.form', ['pkg' => new Packaging, 'orders' => $orders, 'warehouses' => $warehouses]);
    }

    public function store(Request $request, NumberService $numbers)
    {
        $data = $request->validate([
            'production_order_id' => 'nullable|exists:production_orders,id',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'packages_planned' => 'required|integer|min:1',
            'package_type' => 'required|in:BOX,TRAY,POUCH,BOTTLE',
            'notes' => 'nullable|string',
        ]);
        $order = ! empty($data['production_order_id']) ? ProductionOrder::find($data['production_order_id']) : null;
        if ($order) {
            $this->ensureOrgAccess($order);
        }
        if (! empty($data['warehouse_id'])) {
            $this->ensureWarehouse((int) $data['warehouse_id']);
        }
        $pkg = Packaging::create([
            'organization_id' => $request->user()->organization_id,
            'central_kitchen_id' => $order?->central_kitchen_id ?? $request->user()->central_kitchen_id ?? CentralKitchen::first()->id,
            'production_order_id' => $order?->id,
            'warehouse_id' => $data['warehouse_id'] ?? null,
            'number' => $numbers->next('PKG'),
            'packaging_date' => now()->toDateString(),
            'packages_planned' => $data['packages_planned'],
            'package_type' => $data['package_type'],
            'status' => 'DRAFT',
            'notes' => $data['notes'] ?? null,
            'created_by' => $request->user()->id,
        ]);
        if ($order) {
            $pkg->items()->create(['product_id' => $order->product_id, 'qty_packed' => 0]);
        }

        return redirect()->route('packagings.show', $pkg)->with('success', 'Packaging dibuat.');
    }

    public function show(Packaging $pkg)
    {
        $this->ensureOrgAccess($pkg);
        $pkg->load(['items.product', 'productionOrder', 'materialUsages.ingredient.unit', 'materialUsages.batch']);
        $materials = Ingredient::where('organization_id', $pkg->organization_id)->where('category', 'PACKAGING')->active()->with('unit')->get();

        return view('packagings.show', compact('pkg', 'materials'));
    }

    /** Catat pemakaian material kemasan (keluar stok via ledger). */
    public function useMaterial(Request $request, Packaging $pkg, InventoryService $inventory)
    {
        $this->ensureOrgAccess($pkg);
        $data = $request->validate([
            'ingredient_id' => 'required|exists:ingredients,id',
            'qty' => 'required|numeric|min:0.001',
        ]);
        $ing = Ingredient::findOrFail($data['ingredient_id']);
        $this->ensureOrgAccess($ing);
        abort_unless($ing->category === 'PACKAGING', 422, 'Hanya bahan kategori PACKAGING.');
        abort_unless($pkg->warehouse_id, 422, 'Packaging belum punya gudang — isi saat pembuatan.');

        try {
            $allocs = $inventory->consume($pkg->warehouse_id, 'ingredient', $ing->id, (float) $data['qty'], [
                'organization_id' => $pkg->organization_id,
                'movement_type' => 'PRODUCTION_CONSUMPTION',
                'unit_id' => $ing->unit_id,
                'reference_type' => Packaging::class,
                'reference_id' => $pkg->id,
                'reference_no' => $pkg->number,
                'notes' => 'Pemakaian material kemasan',
            ]);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
        $pkg->materialUsages()->create([
            'ingredient_id' => $ing->id,
            'batch_id' => $allocs[0]['batch_id'] ?? null,
            'qty_used' => $data['qty'],
        ]);

        return back()->with('success', 'Pemakaian material tercatat.');
    }

    public function complete(Request $request, Packaging $pkg)
    {
        $this->ensureOrgAccess($pkg);
        $request->validate(['packages_done' => 'required|integer|min:1']);
        abort_unless($pkg->status === 'DRAFT', 422);
        $pkg->update(['packages_done' => $request->packages_done, 'status' => 'COMPLETED']);
        foreach ($pkg->items as $item) {
            $item->update(['qty_packed' => $request->packages_done]);
        }

        return back()->with('success', 'Packaging selesai.');
    }
}
