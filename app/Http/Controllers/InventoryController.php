<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\FiltersRequests;
use App\Models\Ingredient;
use App\Models\InventoryMovement;
use App\Models\InventoryStock;
use App\Models\Product;
use App\Models\Warehouse;
use App\Services\InventoryService;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    use FiltersRequests;

    public function index(Request $request)
    {
        $warehouses = Warehouse::when($request->user()->central_kitchen_id, fn ($q) => $q->where('central_kitchen_id', $request->user()->central_kitchen_id))->get();
        $query = InventoryStock::with(['warehouse', 'batch'])
            ->when($request->filled('warehouse_id'), fn ($q) => $q->where('warehouse_id', $request->warehouse_id))
            ->when($warehouses->isNotEmpty(), fn ($q) => $q->whereIn('warehouse_id', $warehouses->pluck('id')))
            ->when($request->filled('item_type'), fn ($q) => $q->where('item_type', $request->item_type))
            ->where('qty', '>', 0)
            ->orderByDesc('qty');
        $stocks = $query->paginate(20)->withQueryString();

        // Resolve nama item
        $stocks->getCollection()->transform(function ($s) {
            $s->item_name = $s->item_type === 'product'
                ? Product::whereKey($s->item_id)->value('name')
                : Ingredient::whereKey($s->item_id)->value('name');

            return $s;
        });

        return view('inventory.index', compact('stocks', 'warehouses'));
    }

    public function movements(Request $request)
    {
        $warehouses = Warehouse::when($request->user()->central_kitchen_id, fn ($q) => $q->where('central_kitchen_id', $request->user()->central_kitchen_id))->get();
        $query = InventoryMovement::with(['warehouse', 'batch'])
            ->when($warehouses->isNotEmpty(), fn ($q) => $q->whereIn('warehouse_id', $warehouses->pluck('id')))
            ->when($request->filled('warehouse_id'), fn ($q) => $q->where('warehouse_id', $request->warehouse_id))
            ->when($request->filled('type'), fn ($q) => $q->where('movement_type', $request->type))
            ->latest();
        $movements = $query->paginate(25)->withQueryString();
        $types = config('mbg.movement_types');

        return view('inventory.movements', compact('movements', 'warehouses', 'types'));
    }

    public function adjustForm(Request $request)
    {
        $warehouses = Warehouse::when($request->user()->central_kitchen_id, fn ($q) => $q->where('central_kitchen_id', $request->user()->central_kitchen_id))->get();
        $ingredients = Ingredient::active()->where('organization_id', $request->user()->organization_id)->get();

        return view('inventory.adjust', compact('warehouses', 'ingredients'));
    }

    public function adjust(Request $request, InventoryService $inventory)
    {
        $data = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'ingredient_id' => 'required|exists:ingredients,id',
            'batch_id' => 'nullable|exists:batches,id',
            'new_qty' => 'required|numeric|min:0',
            'notes' => 'required|string|min:5',
        ]);
        try {
            $inventory->adjust($data['warehouse_id'], 'ingredient', $data['ingredient_id'], $data['batch_id'] ?? null, (float) $data['new_qty'], [
                'organization_id' => $request->user()->organization_id,
                'reference_type' => 'MANUAL_ADJUST',
                'reference_id' => (int) (now()->timestamp % 1000000),
                'notes' => $data['notes'].' (oleh '.$request->user()->name.')',
            ]);
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('inventory.index')->with('success', 'Penyesuaian stok diposting ke ledger.');
    }

    public function transferForm(Request $request)
    {
        $warehouses = Warehouse::when($request->user()->central_kitchen_id, fn ($q) => $q->where('central_kitchen_id', $request->user()->central_kitchen_id))->get();
        $ingredients = Ingredient::active()->where('organization_id', $request->user()->organization_id)->with('unit')->get();

        return view('inventory.transfer', compact('warehouses', 'ingredients'));
    }

    public function transfer(Request $request, InventoryService $inventory)
    {
        $data = $request->validate([
            'from_warehouse_id' => 'required|exists:warehouses,id|different:to_warehouse_id',
            'to_warehouse_id' => 'required|exists:warehouses,id',
            'ingredient_id' => 'required|exists:ingredients,id',
            'qty' => 'required|numeric|min:0.001',
            'notes' => 'nullable|string',
        ]);
        try {
            $inventory->transfer(
                (int) $data['from_warehouse_id'], (int) $data['to_warehouse_id'],
                'ingredient', (int) $data['ingredient_id'], (float) $data['qty'],
                [
                    'organization_id' => $request->user()->organization_id,
                    'reference_type' => 'MANUAL_TRANSFER',
                    'reference_id' => (int) (now()->timestamp % 1000000),
                    'notes' => ($data['notes'] ?? 'Transfer manual').' (oleh '.$request->user()->name.')',
                ]
            );
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('inventory.index')->with('success', 'Transfer antar gudang berhasil (FEFO + batch baru di tujuan).');
    }

    public function reserveForm(Request $request)
    {
        $warehouses = Warehouse::when($request->user()->central_kitchen_id, fn ($q) => $q->where('central_kitchen_id', $request->user()->central_kitchen_id))->get();
        $ingredients = Ingredient::active()->where('organization_id', $request->user()->organization_id)->with('unit')->get();
        $products = Product::active()->where('organization_id', $request->user()->organization_id)->get();

        return view('inventory.reserve', compact('warehouses', 'ingredients', 'products'));
    }

    public function reserve(Request $request, InventoryService $inventory)
    {
        $data = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'item_type' => 'required|in:ingredient,product',
            'item_id' => 'required|integer|min:1',
            'qty' => 'required|numeric|min:0.001',
        ]);
        try {
            $inventory->reserve((int) $data['warehouse_id'], $data['item_type'], (int) $data['item_id'], (float) $data['qty']);
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Stok berhasil direservasi.');
    }

    public function release(Request $request, InventoryService $inventory)
    {
        $data = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'item_type' => 'required|in:ingredient,product',
            'item_id' => 'required|integer|min:1',
            'qty' => 'required|numeric|min:0.001',
        ]);
        $inventory->releaseReservation((int) $data['warehouse_id'], $data['item_type'], (int) $data['item_id'], (float) $data['qty']);

        return back()->with('success', 'Reservasi dilepas.');
    }
}
