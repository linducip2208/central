<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesOrgAccess;
use App\Http\Controllers\Concerns\FiltersRequests;
use App\Models\Ingredient;
use App\Models\InventoryMovement;
use App\Models\InventoryStock;
use App\Models\Unit;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class IngredientController extends Controller
{
    use AuthorizesOrgAccess, FiltersRequests;

    public function index(Request $request)
    {
        $query = Ingredient::with(['unit'])->where('organization_id', $request->user()->organization_id);
        if ($request->filled('active')) {
            $query->where('is_active', $request->boolean('active'));
        }
        $ingredients = $this->tableQuery($request, $query, ['name', 'code']);
        $warehouses = Warehouse::when($request->user()->central_kitchen_id, fn ($q) => $q->where('central_kitchen_id', $request->user()->central_kitchen_id))->get();

        return view('ingredients.index', compact('ingredients', 'warehouses'));
    }

    public function create(Request $request)
    {
        $units = Unit::where('is_active', true)->get();

        return view('ingredients.form', ['ingredient' => new Ingredient, 'units' => $units]);
    }

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'category' => 'required|in:STAPLE,PROTEIN,VEGETABLE,FRUIT,SPICE,OIL,OTHER,PACKAGING',
            'unit_id' => 'required|exists:units,id',
            'standard_price' => 'required|numeric|min:0',
            'min_stock' => 'nullable|numeric|min:0', 'max_stock' => 'nullable|numeric|min:0',
            'shelf_life_days' => 'nullable|integer|min:0',
            'reorder_point' => 'nullable|numeric|min:0', 'safety_stock' => 'nullable|numeric|min:0',
            'lead_time_days' => 'nullable|integer|min:0', 'moq' => 'nullable|numeric|min:0',
            'preferred_supplier_id' => 'nullable|exists:suppliers,id',
            'allergens' => 'nullable|array', 'allergens.*' => 'exists:allergens,id',
            'is_active' => 'boolean',
        ];
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());
        $allergens = $data['allergens'] ?? [];
        unset($data['allergens']);
        $data['organization_id'] = $request->user()->organization_id;
        $data['code'] = 'ING-'.now()->format('ymd').'-'.strtoupper(substr(uniqid(), -4));
        $data['is_active'] = $request->boolean('is_active', true);
        $ingredient = Ingredient::create($data);
        $ingredient->allergens()->sync($allergens);

        return redirect()->route('ingredients.show', $ingredient)->with('success', 'Bahan baku ditambahkan.');
    }

    public function show(Request $request, Ingredient $ingredient)
    {
        $this->ensureOrgAccess($ingredient);
        $ingredient->load(['allergens', 'preferredSupplier', 'unit']);
        $stocks = InventoryStock::with(['warehouse', 'batch'])
            ->where('item_type', 'ingredient')->where('item_id', $ingredient->id)
            ->when($request->user()->central_kitchen_id, fn ($q) => $q->whereHas('warehouse', fn ($w) => $w->where('central_kitchen_id', $request->user()->central_kitchen_id)))
            ->get();
        $movements = InventoryMovement::where('item_type', 'ingredient')->where('item_id', $ingredient->id)->latest()->take(20)->get();

        return view('ingredients.show', compact('ingredient', 'stocks', 'movements'));
    }

    public function edit(Request $request, Ingredient $ingredient)
    {
        $this->ensureOrgAccess($ingredient);
        $units = Unit::where('is_active', true)->get();

        return view('ingredients.form', compact('ingredient', 'units'));
    }

    public function update(Request $request, Ingredient $ingredient)
    {
        $this->ensureOrgAccess($ingredient);
        $data = $request->validate($this->rules());
        $allergens = $data['allergens'] ?? [];
        unset($data['allergens']);
        $data['is_active'] = $request->boolean('is_active', true);
        $ingredient->update($data);
        $ingredient->allergens()->sync($allergens);

        return redirect()->route('ingredients.show', $ingredient)->with('success', 'Bahan baku diperbarui.');
    }

    public function destroy(Ingredient $ingredient)
    {
        $this->ensureOrgAccess($ingredient);
        if (InventoryStock::where('item_type', 'ingredient')->where('item_id', $ingredient->id)->where('qty', '>', 0)->exists()) {
            return back()->with('error', 'Bahan masih memiliki stok, tidak dapat dihapus. Nonaktifkan saja.');
        }
        $ingredient->delete();

        return redirect()->route('ingredients.index')->with('success', 'Bahan dihapus.');
    }
}
