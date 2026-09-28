<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesOrgAccess;
use App\Models\Allergen;
use App\Models\CentralKitchen;
use App\Models\Delivery;
use App\Models\DeliveryRoute;
use App\Models\MealGroup;
use App\Models\ProductionOrder;
use App\Models\Vehicle;
use App\Models\WorkCenter;
use Illuminate\Http\Request;

class MasterCatalogController extends Controller
{
    use AuthorizesOrgAccess;

    protected function kitchens(Request $request)
    {
        return CentralKitchen::active()->where('organization_id', $request->user()->organization_id)->get();
    }

    public function allergens()
    {
        $allergens = Allergen::withCount(['ingredients', 'recipients'])->get();

        return view('catalog.allergens', compact('allergens'));
    }

    public function storeAllergen(Request $request)
    {
        $data = $request->validate(['code' => 'required|string|max:20|unique:allergens,code', 'name' => 'required|string|max:255', 'description' => 'nullable|string']);
        Allergen::create($data);

        return back()->with('success', 'Alergen ditambahkan.');
    }

    public function destroyAllergen(Allergen $allergen)
    {
        abort_if($allergen->ingredients()->exists() || $allergen->recipients()->exists(), 422, 'Alergen dipakai data, tidak dapat dihapus.');
        $allergen->delete();

        return back()->with('success', 'Alergen dihapus.');
    }

    public function mealGroups(Request $request)
    {
        $groups = MealGroup::where('organization_id', $request->user()->organization_id)->withCount('recipients')->get();

        return view('catalog.meal-groups', compact('groups'));
    }

    public function storeMealGroup(Request $request)
    {
        $data = $request->validate(['code' => 'required|string|max:30|unique:meal_groups,code', 'name' => 'required|string|max:255', 'dietary_notes' => 'nullable|string']);
        MealGroup::create($data + ['organization_id' => $request->user()->organization_id, 'is_active' => true]);

        return back()->with('success', 'Kelompok diet ditambahkan.');
    }

    public function destroyMealGroup(MealGroup $group)
    {
        $this->ensureOrgAccess($group);
        $group->delete();

        return back()->with('success', 'Kelompok diet dihapus.');
    }

    public function vehicles(Request $request)
    {
        $vehicles = Vehicle::where('organization_id', $request->user()->organization_id)->get();
        $kitchens = $this->kitchens($request);

        return view('catalog.vehicles', compact('vehicles', 'kitchens'));
    }

    public function storeVehicle(Request $request)
    {
        if ($request->filled('central_kitchen_id')) {
            $this->ensureKitchen((int) $request->get('central_kitchen_id'));
        }
        $data = $request->validate([
            'central_kitchen_id' => 'nullable|exists:central_kitchens,id',
            'plate_no' => 'required|string|max:20|unique:vehicles,plate_no',
            'name' => 'required|string|max:255',
            'vehicle_type' => 'required|in:BOX,PICKUP,MOTOR,VAN',
            'capacity_portions' => 'nullable|integer|min:0',
        ]);
        Vehicle::create($data + ['organization_id' => $request->user()->organization_id, 'has_cooler' => $request->boolean('has_cooler'), 'status' => 'ACTIVE']);

        return back()->with('success', 'Kendaraan ditambahkan.');
    }

    public function destroyVehicle(Vehicle $vehicle)
    {
        $this->ensureOrgAccess($vehicle);
        abort_if(DeliveryRoute::where('vehicle_id', $vehicle->id)->exists() || Delivery::where('vehicle_id', $vehicle->id)->exists(), 422, 'Kendaraan dipakai rute/delivery.');
        $vehicle->delete();

        return back()->with('success', 'Kendaraan dihapus.');
    }

    public function workCenters(Request $request)
    {
        $centers = WorkCenter::whereHas('centralKitchen', fn ($q) => $q->where('organization_id', $request->user()->organization_id))->with('centralKitchen')->get();
        $kitchens = $this->kitchens($request);

        return view('catalog.work-centers', compact('centers', 'kitchens'));
    }

    public function storeWorkCenter(Request $request)
    {
        $this->ensureKitchen((int) $request->get('central_kitchen_id'));
        $data = $request->validate([
            'central_kitchen_id' => 'required|exists:central_kitchens,id',
            'code' => 'required|string|max:30|unique:work_centers,code',
            'name' => 'required|string|max:255',
            'center_type' => 'required|string|max:30',
            'capacity_per_hour' => 'nullable|integer|min:0',
            'operators_required' => 'nullable|integer|min:0',
        ]);
        WorkCenter::create($data + ['status' => 'ACTIVE']);

        return back()->with('success', 'Work center ditambahkan.');
    }

    public function destroyWorkCenter(WorkCenter $center)
    {
        abort_if(ProductionOrder::where('work_center_id', $center->id)->exists(), 422, 'Work center dipakai production order.');
        $center->delete();

        return back()->with('success', 'Work center dihapus.');
    }
}
