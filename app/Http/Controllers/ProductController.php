<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesOrgAccess;
use App\Http\Controllers\Concerns\FiltersRequests;
use App\Models\InventoryStock;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    use AuthorizesOrgAccess, FiltersRequests;

    public function index(Request $request)
    {
        $products = $this->tableQuery($request, Product::with(['unit'])->where('organization_id', $request->user()->organization_id), ['name', 'code']);

        return view('products.index', compact('products'));
    }

    public function create(Request $request)
    {
        $units = Unit::where('is_active', true)->get();

        return view('products.form', ['product' => new Product, 'units' => $units]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|in:MEAL,SNACK,DRINK,EXTRA',
            'unit_id' => 'required|exists:units,id',
            'portion_size_gram' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
        ]);
        $data['organization_id'] = $request->user()->organization_id;
        $data['code'] = 'PRD-'.now()->format('ymd').'-'.strtoupper(substr(uniqid(), -4));
        $data['is_active'] = $request->boolean('is_active', true);
        $product = Product::create($data);

        return redirect()->route('products.show', $product)->with('success', 'Produk ditambahkan. Lengkapi resepnya.');
    }

    public function show(Product $product)
    {
        $this->ensureOrgAccess($product);
        $product->load(['recipes.items.ingredient', 'recipes.items.unit', 'activeRecipe']);
        $stocks = InventoryStock::with(['warehouse', 'batch'])->where('item_type', 'product')->where('item_id', $product->id)->get();

        return view('products.show', compact('product', 'stocks'));
    }

    public function edit(Request $request, Product $product)
    {
        $this->ensureOrgAccess($product);
        $units = Unit::where('is_active', true)->get();

        return view('products.form', compact('product', 'units'));
    }

    public function update(Request $request, Product $product)
    {
        $this->ensureOrgAccess($product);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|in:MEAL,SNACK,DRINK,EXTRA',
            'unit_id' => 'required|exists:units,id',
            'portion_size_gram' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);
        $product->update($data);

        return redirect()->route('products.show', $product)->with('success', 'Produk diperbarui.');
    }

    public function destroy(Product $product)
    {
        $this->ensureOrgAccess($product);
        if ($product->recipes()->exists() || InventoryStock::where('item_type', 'product')->where('item_id', $product->id)->where('qty', '>', 0)->exists()) {
            return back()->with('error', 'Produk memiliki resep/stok, tidak dapat dihapus.');
        }
        $product->delete();

        return redirect()->route('products.index')->with('success', 'Produk dihapus.');
    }
}
