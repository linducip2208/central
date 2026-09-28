<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesOrgAccess;
use App\Models\CentralKitchen;
use App\Models\Equipment;
use App\Models\Shift;
use Illuminate\Http\Request;

class MaintenanceController extends Controller
{
    use AuthorizesOrgAccess;

    protected function kitchens(Request $request)
    {
        return CentralKitchen::active()->where('organization_id', $request->user()->organization_id)->get();
    }

    public function shifts(Request $request)
    {
        $shifts = Shift::whereHas('centralKitchen', fn ($q) => $q->where('organization_id', $request->user()->organization_id))->with('centralKitchen')->get();
        $kitchens = $this->kitchens($request);

        return view('maintenance.shifts', compact('shifts', 'kitchens'));
    }

    public function storeShift(Request $request)
    {
        $data = $request->validate([
            'central_kitchen_id' => 'required|exists:central_kitchens,id',
            'code' => 'required|string|max:20',
            'name' => 'required|string|max:255',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
        ]);
        $this->ensureKitchen((int) $data['central_kitchen_id']);
        Shift::create($data + ['is_active' => true]);

        return back()->with('success', 'Shift ditambahkan.');
    }

    public function equipment(Request $request)
    {
        $items = Equipment::whereHas('centralKitchen', fn ($q) => $q->where('organization_id', $request->user()->organization_id))->with(['workCenter', 'centralKitchen'])->get();
        $kitchens = $this->kitchens($request);

        return view('maintenance.equipment', compact('items', 'kitchens'));
    }

    public function storeEquipment(Request $request)
    {
        $data = $request->validate([
            'central_kitchen_id' => 'required|exists:central_kitchens,id',
            'work_center_id' => 'nullable|exists:work_centers,id',
            'code' => 'required|string|max:30|unique:equipment,code',
            'name' => 'required|string|max:255',
            'next_maintenance_at' => 'nullable|date',
        ]);
        $this->ensureKitchen((int) $data['central_kitchen_id']);
        Equipment::create($data + ['status' => 'ACTIVE']);

        return back()->with('success', 'Peralatan ditambahkan.');
    }

    public function storeLog(Request $request, Equipment $equipment)
    {
        $data = $request->validate([
            'maintenance_type' => 'required|in:PREVENTIVE,CORRECTIVE',
            'description' => 'required|string',
            'performed_at' => 'required|date',
            'cost' => 'nullable|numeric|min:0',
        ]);
        $equipment->logs()->create($data + ['performed_by' => $request->user()->id]);
        $equipment->update(['last_maintenance_at' => $data['performed_at']]);

        return back()->with('success', 'Log maintenance tercatat.');
    }
}
