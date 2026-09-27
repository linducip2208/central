<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesOrgAccess;
use App\Http\Controllers\Concerns\FiltersRequests;
use App\Models\Ingredient;
use App\Models\Product;
use App\Models\ProductionOrder;
use App\Models\Recipe;
use App\Models\Unit;
use Illuminate\Http\Request;

class RecipeController extends Controller
{
    use AuthorizesOrgAccess, FiltersRequests;

    public function index(Request $request)
    {
        $recipes = $this->tableQuery($request, Recipe::with(['product'])->where('organization_id', $request->user()->organization_id), ['name', 'code']);

        return view('recipes.index', compact('recipes'));
    }

    public function create(Request $request)
    {
        $products = Product::active()->where('organization_id', $request->user()->organization_id)->get();
        $ingredients = Ingredient::active()->where('organization_id', $request->user()->organization_id)->with('unit')->get();
        $units = Unit::where('is_active', true)->get();

        return view('recipes.form', ['recipe' => new Recipe, 'products' => $products, 'ingredients' => $ingredients, 'units' => $units]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'name' => 'required|string|max:255',
            'version' => 'nullable|string|max:10',
            'yield_qty' => 'required|numeric|min:0.01',
            'yield_unit_id' => 'nullable|exists:units,id',
            'instructions' => 'nullable|string',
            'cook_time_minutes' => 'nullable|integer|min:0',
            'items' => 'required|array|min:1',
            'items.*.ingredient_id' => 'required|exists:ingredients,id',
            'items.*.qty' => 'required|numeric|min:0.0001',
            'items.*.unit_id' => 'required|exists:units,id',
            'items.*.waste_factor_pct' => 'nullable|numeric|min:0|max:100',
        ]);
        $data['organization_id'] = $request->user()->organization_id;
        $data['code'] = 'RCP-'.now()->format('ymd').'-'.strtoupper(substr(uniqid(), -4));
        $recipe = Recipe::create(collect($data)->except('items')->toArray());
        foreach ($data['items'] as $it) {
            $recipe->items()->create($it);
        }

        return redirect()->route('recipes.show', $recipe)->with('success', 'Resep berhasil dibuat.');
    }

    public function show(Recipe $recipe)
    {
        $this->ensureOrgAccess($recipe);
        $recipe->load(['product', 'items.ingredient.unit', 'items.unit', 'yieldUnit', 'nutrition' => fn ($q) => $q->latest()->take(1)]);

        return view('recipes.show', compact('recipe'));
    }

    public function storeNutrition(Request $request, Recipe $recipe)
    {
        $this->ensureOrgAccess($recipe);
        $data = $request->validate([
            'calories' => 'nullable|numeric|min:0', 'protein_g' => 'nullable|numeric|min:0',
            'carbs_g' => 'nullable|numeric|min:0', 'fat_g' => 'nullable|numeric|min:0',
            'fiber_g' => 'nullable|numeric|min:0', 'sugar_g' => 'nullable|numeric|min:0',
            'sodium_mg' => 'nullable|numeric|min:0', 'serving_size_g' => 'nullable|numeric|min:0',
        ]);
        $recipe->nutrition()->create($data + ['product_id' => $recipe->product_id, 'source' => 'MANUAL']);

        return back()->with('success', 'Data gizi resep tersimpan.');
    }

    public function toggleActive(Recipe $recipe)
    {
        $this->ensureOrgAccess($recipe);
        $recipe->update(['is_active' => ! $recipe->is_active]);

        return back()->with('success', 'Status resep diperbarui.');
    }

    public function destroy(Recipe $recipe)
    {
        $this->ensureOrgAccess($recipe);
        if ($recipe->product()->exists() && ProductionOrder::where('recipe_id', $recipe->id)->exists()) {
            return back()->with('error', 'Resep sudah dipakai production order, tidak dapat dihapus.');
        }
        $recipe->delete();

        return redirect()->route('recipes.index')->with('success', 'Resep dihapus.');
    }
}
