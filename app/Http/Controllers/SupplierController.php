<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\FiltersRequests;
use App\Models\Ingredient;
use App\Models\Supplier;
use App\Services\NumberService;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    use FiltersRequests;

    public function index(Request $request)
    {
        $suppliers = $this->tableQuery($request, Supplier::query()->where('organization_id', $request->user()->organization_id), ['name', 'code', 'phone']);

        return view('suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('suppliers.form', ['supplier' => new Supplier]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|in:FOOD,NON_FOOD,SERVICE',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'tax_number' => 'nullable|string|max:30',
            'status' => 'required|in:ACTIVE,INACTIVE',
        ]);
        $data['organization_id'] = $request->user()->organization_id;
        $data['code'] = 'SUP-'.now()->format('ymd').'-'.strtoupper(substr(uniqid(), -4));
        $supplier = Supplier::create($data);

        return redirect()->route('suppliers.show', $supplier)->with('success', 'Supplier berhasil ditambahkan.');
    }

    public function show(Supplier $supplier)
    {
        $this->authorizeOrg($supplier);
        $supplier->load([
            'purchaseOrders' => fn ($q) => $q->latest()->take(10),
            'contacts', 'addresses', 'contracts', 'priceLists.ingredient.unit',
        ]);
        $ingredients = Ingredient::active()->where('organization_id', $supplier->organization_id)->get();

        return view('suppliers.show', compact('supplier', 'ingredients'));
    }

    public function storeContact(Request $request, Supplier $supplier)
    {
        $this->authorizeOrg($supplier);
        $data = $request->validate(['name' => 'required|string|max:255', 'position' => 'nullable|string|max:50', 'phone' => 'nullable|string|max:30', 'email' => 'nullable|email']);
        if ($request->boolean('is_primary')) {
            $supplier->contacts()->update(['is_primary' => false]);
        }
        $supplier->contacts()->create($data + ['is_primary' => $request->boolean('is_primary')]);

        return back()->with('success', 'Kontak ditambahkan.');
    }

    public function storeAddress(Request $request, Supplier $supplier)
    {
        $this->authorizeOrg($supplier);
        $data = $request->validate(['label' => 'required|string|max:30', 'address' => 'required|string', 'city' => 'nullable|string|max:100']);
        if ($request->boolean('is_default')) {
            $supplier->addresses()->update(['is_default' => false]);
        }
        $supplier->addresses()->create($data + ['is_default' => $request->boolean('is_default')]);

        return back()->with('success', 'Alamat ditambahkan.');
    }

    public function storeContract(Request $request, Supplier $supplier, NumberService $numbers)
    {
        $this->authorizeOrg($supplier);
        $data = $request->validate([
            'start_date' => 'required|date', 'end_date' => 'required|date|after_or_equal:start_date',
            'payment_terms' => 'required|in:CASH,CREDIT,COD', 'notes' => 'nullable|string',
        ]);
        $supplier->contracts()->create($data + [
            'organization_id' => $supplier->organization_id,
            'number' => $numbers->next('CTR'), 'status' => 'ACTIVE',
        ]);

        return back()->with('success', 'Kontrak ditambahkan.');
    }

    public function storePrice(Request $request, Supplier $supplier)
    {
        $this->authorizeOrg($supplier);
        $data = $request->validate([
            'ingredient_id' => 'required|exists:ingredients,id',
            'price' => 'required|numeric|min:0',
            'moq' => 'nullable|numeric|min:0',
            'lead_time_days' => 'nullable|integer|min:0',
            'effective_from' => 'nullable|date',
        ]);
        $ing = Ingredient::findOrFail($data['ingredient_id']);
        $supplier->priceLists()->updateOrCreate(
            ['ingredient_id' => $ing->id],
            $data + ['unit_id' => $ing->unit_id]
        );

        return back()->with('success', 'Harga supplier diperbarui (dipakai MRP & award).');
    }

    public function edit(Supplier $supplier)
    {
        $this->authorizeOrg($supplier);

        return view('suppliers.form', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $this->authorizeOrg($supplier);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|in:FOOD,NON_FOOD,SERVICE',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'tax_number' => 'nullable|string|max:30',
            'rating' => 'nullable|integer|min:0|max:5',
            'status' => 'required|in:ACTIVE,INACTIVE',
        ]);
        $supplier->update($data);

        return redirect()->route('suppliers.show', $supplier)->with('success', 'Supplier berhasil diperbarui.');
    }

    public function destroy(Supplier $supplier)
    {
        $this->authorizeOrg($supplier);
        if ($supplier->purchaseOrders()->exists()) {
            return back()->with('error', 'Supplier tidak dapat dihapus karena memiliki riwayat purchase order.');
        }
        $supplier->delete();

        return redirect()->route('suppliers.index')->with('success', 'Supplier dihapus.');
    }

    protected function authorizeOrg(Supplier $supplier): void
    {
        abort_unless($supplier->organization_id === request()->user()->organization_id || request()->user()->hasRole(['super-admin']), 403);
    }
}
