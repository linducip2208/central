<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\FiltersRequests;
use App\Models\Supplier;
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
        $supplier->load(['purchaseOrders' => fn ($q) => $q->latest()->take(10)]);

        return view('suppliers.show', compact('supplier'));
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
