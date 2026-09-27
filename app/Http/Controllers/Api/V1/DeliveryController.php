<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use Illuminate\Http\Request;

class DeliveryController extends Controller
{
    public function index(Request $request)
    {
        $deliveries = Delivery::with(['school'])
            ->where('organization_id', $request->user()->organization_id)
            ->when($request->user()->central_kitchen_id, fn ($q) => $q->where('central_kitchen_id', $request->user()->central_kitchen_id))
            ->when($request->boolean('mine'), fn ($q) => $q->where('courier_id', $request->user()->id))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest()->paginate(20);

        return response()->json($deliveries);
    }

    public function show(Request $request, Delivery $delivery)
    {
        abort_unless((int) $delivery->organization_id === (int) $request->user()->organization_id, 403);
        $delivery->load(['items.product', 'school', 'trackings']);

        return response()->json($delivery);
    }

    /** Update posisi/status oleh kurir (tanpa mutasi stok — mutasi tetap via web serah terima). */
    public function track(Request $request, Delivery $delivery)
    {
        abort_unless((int) $delivery->organization_id === (int) $request->user()->organization_id, 403);
        $data = $request->validate([
            'status' => 'required|string|max:30',
            'notes' => 'nullable|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);
        $tracking = $delivery->trackings()->create($data + ['created_by' => $request->user()->id]);
        if (in_array($data['status'], ['IN_TRANSIT']) && $delivery->status === 'PLANNED') {
            $delivery->update(['status' => 'IN_TRANSIT', 'dispatched_at' => now()]);
        }

        return response()->json($tracking, 201);
    }
}
