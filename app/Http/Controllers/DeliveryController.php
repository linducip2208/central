<?php

namespace App\Http\Controllers;

use App\Core\Services\NotificationService;
use App\Events\DeliveryCompleted;
use App\Http\Controllers\Concerns\AuthorizesOrgAccess;
use App\Http\Controllers\Concerns\FiltersRequests;
use App\Models\Delivery;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\InventoryService;
use App\Services\PeriodService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeliveryController extends Controller
{
    use AuthorizesOrgAccess, FiltersRequests;

    public function index(Request $request)
    {
        $query = Delivery::with(['school'])->where('organization_id', $request->user()->organization_id);
        $this->scopeKitchen($request, $query);
        $deliveries = $this->tableQuery($request, $query, ['number']);

        return view('deliveries.index', compact('deliveries'));
    }

    public function show(Delivery $delivery)
    {
        $this->ensureOrgAccess($delivery);
        $delivery->load(['items.product', 'school', 'trackings', 'distribution']);
        $warehouses = Warehouse::where('central_kitchen_id', $delivery->central_kitchen_id)->get();

        return view('deliveries.show', compact('delivery', 'warehouses'));
    }

    /** Serah terima: kurangi stok produk (FEFO) sesuai qty diterima + return. */
    public function deliver(Request $request, Delivery $delivery, InventoryService $inventory, PeriodService $periods)
    {
        $this->ensureOrgAccess($delivery);
        $this->ensureWarehouse((int) $request->get('warehouse_id'));
        $periods->assertOpen($delivery->organization_id, $delivery->central_kitchen_id, now()->toDateString());
        abort_unless(in_array($delivery->status, ['PLANNED', 'IN_TRANSIT']), 422);
        $data = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'qty_delivered' => 'required|integer|min:0',
            'qty_returned' => 'nullable|integer|min:0',
            'received_by_name' => 'required|string|max:255',
            'temperature_c' => 'nullable|numeric|min:0|max:100',
            'proof' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'notes' => 'nullable|string',
        ]);
        $proofPath = $request->hasFile('proof')
            ? $request->file('proof')->store('delivery-proofs', 'public')
            : null;
        $total = $data['qty_delivered'] + ($data['qty_returned'] ?? 0);
        abort_if($total > $delivery->qty_planned, 422, 'Total serah terima melebihi qty rencana.');

        try {
            DB::transaction(function () use ($data, $delivery, $inventory, $request, $proofPath) {
                // Stok keluar sebesar yang dibawa (delivered + returned dibawa pulang lalu dicatat? disederhanakan: keluar = delivered)
                foreach ($delivery->items as $item) {
                    $ratio = $delivery->qty_planned > 0 ? $item->qty_planned / $delivery->qty_planned : 0;
                    $outQty = (int) round($data['qty_delivered'] * $ratio);
                    if ($outQty > 0) {
                        $inventory->consume($data['warehouse_id'], 'product', $item->product_id, $outQty, [
                            'organization_id' => $delivery->organization_id,
                            'movement_type' => 'DELIVERY',
                            'reference_type' => Delivery::class,
                            'reference_id' => $delivery->id,
                            'reference_no' => $delivery->number,
                        ]);
                        $item->update(['qty_delivered' => $item->qty_delivered + $outQty]);
                    }
                }
                $delivery->update(array_filter([
                    'qty_delivered' => $data['qty_delivered'],
                    'qty_returned' => $data['qty_returned'] ?? 0,
                    'status' => $data['qty_delivered'] >= $delivery->qty_planned ? 'DELIVERED' : 'PARTIAL',
                    'delivered_at' => now(),
                    'received_by_name' => $data['received_by_name'],
                    'temperature_c' => $data['temperature_c'] ?? null,
                    'delivery_proof' => $proofPath,
                    'notes' => $data['notes'] ?? null,
                ], fn ($v) => $v !== null));
                $delivery->trackings()->create([
                    'status' => $delivery->fresh()->status,
                    'notes' => 'Diterima oleh '.$data['received_by_name'],
                    'created_by' => $request->user()->id,
                ]);
                foreach ($delivery->distribution->items ?? [] as $di) {
                    if ($di->school_id === $delivery->school_id) {
                        $di->increment('qty_delivered', $data['qty_delivered']);
                    }
                }
            });
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal menyimpan serah terima: '.$e->getMessage());
        }

        event(new DeliveryCompleted($delivery->fresh()));
        $admins = User::where('organization_id', $delivery->organization_id)
            ->whereHas('roles', fn ($q) => $q->whereIn('name', ['super-admin', 'admin']))
            ->get();
        app(NotificationService::class)->send($admins, 'delivery_done', [
            'number' => $delivery->number, 'school' => $delivery->school->name ?? '-',
            'delivered' => $delivery->fresh()->qty_delivered, 'status' => $delivery->fresh()->status,
        ]);

        return back()->with('success', 'Serah terima tersimpan, stok berkurang.');
    }

    public function fail(Request $request, Delivery $delivery)
    {
        $this->ensureOrgAccess($delivery);
        $request->validate(['notes' => 'required|string']);
        abort_unless(in_array($delivery->status, ['PLANNED', 'IN_TRANSIT']), 422);
        $delivery->update(['status' => 'FAILED', 'notes' => $request->notes]);
        $delivery->trackings()->create(['status' => 'FAILED', 'notes' => $request->notes, 'created_by' => $request->user()->id]);

        return back()->with('success', 'Delivery ditandai gagal.');
    }

    public function track(Request $request, Delivery $delivery)
    {
        $this->ensureOrgAccess($delivery);
        $data = $request->validate(['status' => 'required|string|max:30', 'notes' => 'nullable|string', 'latitude' => 'nullable|numeric|between:-90,90', 'longitude' => 'nullable|numeric|between:-180,180']);
        $delivery->trackings()->create($data + ['created_by' => $request->user()->id]);

        return back()->with('success', 'Tracking ditambahkan.');
    }
}
