<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\FiltersRequests;
use App\Models\CentralKitchen;
use App\Models\KitchenUnit;
use App\Models\Organization;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class FacilityController extends Controller
{
    use FiltersRequests;

    // ---- Organizations ----
    public function orgIndex(Request $request)
    {
        $orgs = $this->tableQuery($request, Organization::query(), ['name', 'code']);

        return view('organizations.index', compact('orgs'));
    }

    public function orgStore(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:255', 'city' => 'nullable|string|max:100', 'phone' => 'nullable|string|max:30']);
        $data['code'] = 'ORG-'.now()->format('ymd').'-'.strtoupper(substr(uniqid(), -4));
        $data['status'] = 'ACTIVE';
        Organization::create($data);

        return back()->with('success', 'Organisasi ditambahkan.');
    }

    // ---- Central Kitchens ----
    public function kitchenIndex(Request $request)
    {
        $query = CentralKitchen::with(['organization'])->where('organization_id', $request->user()->organization_id);
        $kitchens = $this->tableQuery($request, $query, ['name', 'code']);
        $orgs = Organization::all();

        return view('facilities.kitchens', compact('kitchens', 'orgs'));
    }

    public function kitchenStore(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255', 'city' => 'nullable|string|max:100',
            'pic_name' => 'nullable|string|max:255', 'pic_phone' => 'nullable|string|max:30',
            'daily_capacity' => 'nullable|integer|min:0',
            'latitude' => 'nullable|numeric|between:-90,90', 'longitude' => 'nullable|numeric|between:-180,180',
        ]);
        $data['organization_id'] = $request->user()->organization_id;
        $data['code'] = 'CK-'.now()->format('ymd').'-'.strtoupper(substr(uniqid(), -4));
        $data['status'] = 'ACTIVE';
        CentralKitchen::create($data);

        return back()->with('success', 'Central kitchen ditambahkan.');
    }

    public function kitchenShow(CentralKitchen $kitchen)
    {
        $kitchen->load(['kitchenUnits', 'warehouses', 'schools']);

        return view('facilities.kitchen-show', compact('kitchen'));
    }

    // ---- Kitchen Units ----
    public function unitIndex(Request $request)
    {
        $query = KitchenUnit::with(['centralKitchen'])->whereHas('centralKitchen', fn ($q) => $q->where('organization_id', $request->user()->organization_id));
        $units = $this->tableQuery($request, $query, ['name', 'code']);
        $kitchens = CentralKitchen::active()->where('organization_id', $request->user()->organization_id)->get();

        return view('facilities.units', compact('units', 'kitchens'));
    }

    public function unitStore(Request $request)
    {
        $data = $request->validate([
            'central_kitchen_id' => 'required|exists:central_kitchens,id',
            'name' => 'required|string|max:255',
            'unit_type' => 'required|in:PRODUCTION,PACKAGING,STORAGE',
            'capacity' => 'nullable|integer|min:0',
        ]);
        $data['code'] = 'KU-'.now()->format('ymd').'-'.strtoupper(substr(uniqid(), -4));
        $data['status'] = 'ACTIVE';
        KitchenUnit::create($data);

        return back()->with('success', 'Unit dapur ditambahkan.');
    }

    // ---- Warehouses ----
    public function warehouseIndex(Request $request)
    {
        $query = Warehouse::with(['centralKitchen'])->whereHas('centralKitchen', fn ($q) => $q->where('organization_id', $request->user()->organization_id));
        $warehouses = $this->tableQuery($request, $query, ['name', 'code']);
        $kitchens = CentralKitchen::active()->where('organization_id', $request->user()->organization_id)->get();

        return view('facilities.warehouses', compact('warehouses', 'kitchens'));
    }

    public function warehouseStore(Request $request)
    {
        $data = $request->validate([
            'central_kitchen_id' => 'required|exists:central_kitchens,id',
            'name' => 'required|string|max:255',
            'warehouse_type' => 'required|in:DRY,CHILLED,FROZEN,PACKAGING',
            'fifo_method' => 'required|in:FEFO,FIFO',
            'location' => 'nullable|string', 'pic_name' => 'nullable|string|max:255',
        ]);
        $data['code'] = 'WH-'.now()->format('ymd').'-'.strtoupper(substr(uniqid(), -4));
        $data['status'] = 'ACTIVE';
        Warehouse::create($data);

        return back()->with('success', 'Gudang ditambahkan.');
    }
}
