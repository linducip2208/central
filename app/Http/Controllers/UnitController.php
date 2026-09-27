<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesOrgAccess;
use App\Http\Controllers\Concerns\FiltersRequests;
use App\Models\Ingredient;
use App\Models\Product;
use App\Models\Unit;
use App\Models\UnitConversion;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    use AuthorizesOrgAccess, FiltersRequests;

    public function index(Request $request)
    {
        $units = $this->tableQuery($request, Unit::query(), ['code', 'name', 'symbol']);

        return view('units.index', compact('units'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|max:20|unique:units,code',
            'name' => 'required|string|max:255',
            'symbol' => 'required|string|max:10',
            'unit_type' => 'required|in:WEIGHT,VOLUME,COUNT,LENGTH',
            'is_base' => 'boolean',
        ]);
        $data['is_active'] = true;
        Unit::create($data);

        return back()->with('success', 'Satuan ditambahkan.');
    }

    public function update(Request $request, Unit $unit)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'symbol' => 'required|string|max:10',
            'unit_type' => 'required|in:WEIGHT,VOLUME,COUNT,LENGTH',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);
        $unit->update($data);

        return back()->with('success', 'Satuan diperbarui.');
    }

    public function destroy(Unit $unit)
    {
        if ($unit->conversionsFrom()->exists() || $unit->conversionsTo()->exists()
            || Ingredient::where('unit_id', $unit->id)->exists()
            || Product::where('unit_id', $unit->id)->exists()) {
            return back()->with('error', 'Satuan dipakai bahan/produk/konversi, tidak dapat dihapus. Nonaktifkan saja.');
        }
        $unit->delete();

        return back()->with('success', 'Satuan dihapus.');
    }

    public function storeConversion(Request $request, Unit $unit)
    {
        $data = $request->validate([
            'to_unit_id' => 'required|exists:units,id|different:from_unit',
            'factor' => 'required|numeric|min:0.000001',
        ]);
        abort_if((int) $data['to_unit_id'] === (int) $unit->id, 422, 'Satuan tujuan harus berbeda.');
        UnitConversion::updateOrCreate(
            ['from_unit_id' => $unit->id, 'to_unit_id' => $data['to_unit_id']],
            ['factor' => $data['factor']]
        );

        return back()->with('success', 'Konversi tersimpan.');
    }

    public function destroyConversion(UnitConversion $conversion)
    {
        $conversion->delete();

        return back()->with('success', 'Konversi dihapus.');
    }
}
