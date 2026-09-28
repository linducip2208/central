<?php

namespace App\Http\Controllers;

use App\Core\Services\NotificationService;
use App\Events\ProductionCompleted;
use App\Http\Controllers\Concerns\AuthorizesOrgAccess;
use App\Http\Controllers\Concerns\FiltersRequests;
use App\Models\Downtime;
use App\Models\InventoryStock;
use App\Models\ProductionOrder;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WorkCenter;
use App\Services\CostingService;
use App\Services\InventoryService;
use App\Services\PeriodService;
use App\Services\WebhookService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductionOrderController extends Controller
{
    use AuthorizesOrgAccess, FiltersRequests;

    public function index(Request $request)
    {
        $query = ProductionOrder::with(['product'])->where('organization_id', $request->user()->organization_id);
        $this->scopeKitchen($request, $query);
        $orders = $this->tableQuery($request, $query, ['number']);

        return view('production-orders.index', compact('orders'));
    }

    public function show(ProductionOrder $order, InventoryService $inventory)
    {
        $this->ensureOrgAccess($order);
        $order->load(['items.ingredient.unit', 'items.ingredient.approvedSubstitutes.substitute', 'product.unit', 'recipe', 'kitchenUnit', 'workCenter', 'shift', 'operators.user']);
        $warehouses = Warehouse::where('central_kitchen_id', $order->central_kitchen_id)->get();
        $workCenters = WorkCenter::where('central_kitchen_id', $order->central_kitchen_id)->active()->get();
        $staff = User::where('organization_id', $order->organization_id)->active()->orderBy('name')->take(100)->get();
        // Cek ketersediaan teoritis per bahan (untuk material check).
        $availability = [];
        foreach ($order->items as $it) {
            $need = max(0, (float) $it->qty_required - (float) $it->qty_consumed);
            $avail = 0;
            foreach ($warehouses as $w) {
                $avail += $inventory->availableOf($w->id, 'ingredient', $it->ingredient_id);
            }
            $availability[$it->id] = ['need' => $need, 'available' => $avail, 'ok' => $avail >= $need - 1e-9];
        }
        $downtimes = Downtime::where('production_order_id', $order->id)->latest()->take(10)->get();

        return view('production-orders.show', compact('order', 'warehouses', 'workCenters', 'staff', 'availability', 'downtimes'));
    }

    public function assignWorkCenter(Request $request, ProductionOrder $order)
    {
        $this->ensureOrgAccess($order);
        $request->validate(['work_center_id' => 'nullable|exists:work_centers,id']);
        $order->update(['work_center_id' => $request->work_center_id]);

        return back()->with('success', 'Work center diperbarui.');
    }

    public function assignOperator(Request $request, ProductionOrder $order)
    {
        $this->ensureOrgAccess($order);
        $data = $request->validate(['user_id' => 'required|exists:users,id', 'role' => 'required|in:OPERATOR,SUPERVISOR,QC']);
        $order->operators()->updateOrCreate(['user_id' => $data['user_id']], $data + ['assigned_at' => now()]);

        return back()->with('success', 'Operator ditugaskan.');
    }

    public function materialCheck(ProductionOrder $order, InventoryService $inventory)
    {
        $this->ensureOrgAccess($order);
        abort_unless(in_array($order->status, ['RELEASED', 'PLANNED']), 422);
        $warehouses = Warehouse::where('central_kitchen_id', $order->central_kitchen_id)->pluck('id');
        $short = [];
        foreach ($order->items as $it) {
            $need = max(0, (float) $it->qty_required - (float) $it->qty_consumed);
            $avail = InventoryStock::whereIn('warehouse_id', $warehouses)->where('item_type', 'ingredient')->where('item_id', $it->ingredient_id)->selectRaw('SUM(qty - reserved_qty) as a')->value('a') ?? 0;
            if ($avail < $need - 1e-9) {
                $short[] = ($it->ingredient->name ?? '#'.$it->ingredient_id).' kurang '.number_format($need - $avail, 2);
            }
        }
        // Biaya teoritis dari kebutuhan × harga standar.
        $theoretical = 0;
        foreach ($order->items as $it) {
            $theoretical += (float) $it->qty_required * (float) ($it->ingredient->standard_price ?? 0);
        }
        $order->update(['material_status' => empty($short) ? 'CHECKED' : 'PENDING', 'theoretical_cost' => $theoretical]);

        return back()->with(empty($short) ? 'success' : 'error', empty($short) ? 'Material check lulus. Biaya teoritis '.mbg_currency($theoretical).'.' : 'Stok kurang: '.implode('; ', $short));
    }

    public function recordDowntime(Request $request, ProductionOrder $order)
    {
        $this->ensureOrgAccess($order);
        $data = $request->validate([
            'work_center_id' => 'nullable|exists:work_centers,id',
            'reason' => 'required|string|max:60',
            'started_at' => 'required|date',
            'ended_at' => 'nullable|date|after_or_equal:started_at',
            'notes' => 'nullable|string',
        ]);
        Downtime::create($data + ['organization_id' => $order->organization_id, 'central_kitchen_id' => $order->central_kitchen_id, 'production_order_id' => $order->id]);

        return back()->with('success', 'Downtime dicatat.');
    }

    public function release(ProductionOrder $order)
    {
        $this->ensureOrgAccess($order);
        abort_unless($order->status === 'PLANNED', 422);
        abort_unless($order->items()->exists(), 422, 'Order tanpa kebutuhan bahan (resep kosong).');
        $order->update(['status' => 'RELEASED']);
        app(NotificationService::class)->sendProductionReady($order->id, $order->organization_id);

        return back()->with('success', 'Order dirilis ke dapur.');
    }

    public function start(ProductionOrder $order)
    {
        $this->ensureOrgAccess($order);
        abort_unless(in_array($order->status, ['RELEASED', 'PARTIAL']), 422);
        $order->update(['status' => 'IN_PROGRESS', 'started_at' => $order->started_at ?? now()]);
        app(WebhookService::class)->dispatch('production.started', ['number' => $order->number], $order->organization_id);

        return back()->with('success', 'Produksi dimulai.');
    }

    /** Konsumsi bahan dari gudang (FEFO, bisa parsial). */
    public function consume(Request $request, ProductionOrder $order, InventoryService $inventory)
    {
        abort_unless(in_array($order->status, ['RELEASED', 'IN_PROGRESS', 'PARTIAL']), 422, 'Order belum dirilis.');
        $this->ensureWarehouse((int) $request->get('warehouse_id'));
        $data = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'items' => 'required|array|min:1',
            'items.*.order_item_id' => 'required|exists:production_order_items,id',
            'items.*.qty' => 'required|numeric|min:0.001',
        ]);
        try {
            DB::transaction(function () use ($data, $order, $inventory) {
                foreach ($data['items'] as $it) {
                    $oi = $order->items->firstWhere('id', $it['order_item_id']);
                    abort_unless($oi && $oi->production_order_id === $order->id, 422);
                    $allocs = $inventory->consume($data['warehouse_id'], 'ingredient', $oi->ingredient_id, (float) $it['qty'], [
                        'organization_id' => $order->organization_id,
                        'movement_type' => 'PRODUCTION_CONSUMPTION',
                        'unit_id' => $oi->unit_id,
                        'reference_type' => ProductionOrder::class,
                        'reference_id' => $order->id,
                        'reference_no' => $order->number,
                    ]);
                    $oi->increment('qty_consumed', array_sum(array_column($allocs, 'qty')));
                }
                if ($order->status === 'RELEASED') {
                    $order->update(['status' => 'IN_PROGRESS', 'started_at' => now()]);
                }
            });
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal konsumsi bahan: '.$e->getMessage());
        }

        return back()->with('success', 'Konsumsi bahan tercatat (FEFO).');
    }

    /** Selesaikan produksi: catat output ke stok + costing otomatis. */
    public function complete(Request $request, ProductionOrder $order, InventoryService $inventory, CostingService $costing, PeriodService $periods)
    {
        abort_unless($order->isCompletable(), 422);
        $this->ensureWarehouse((int) $request->get('warehouse_id'));
        $periods->assertOpen($order->organization_id, $order->central_kitchen_id, now()->toDateString());
        $data = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'produced_qty' => 'required|numeric|min:0',
            'rejected_qty' => 'nullable|numeric|min:0',
            'rework_qty' => 'nullable|numeric|min:0',
            'shift_id' => 'nullable|exists:shifts,id',
            'expiry_date' => 'nullable|date|after:today',
            'labor_cost' => 'nullable|numeric|min:0',
            'overhead_cost' => 'nullable|numeric|min:0',
        ]);
        try {
            DB::transaction(function () use ($data, $order, $inventory, $costing) {
                $produced = (float) $data['produced_qty'];
                if ($produced > 0) {
                    $inventory->produceOutput([
                        'organization_id' => $order->organization_id,
                        'warehouse_id' => $data['warehouse_id'],
                        'item_id' => $order->product_id,
                        'qty' => $produced,
                        'unit_id' => $order->unit_id,
                        'unit_cost' => 0,
                        'expiry_date' => $data['expiry_date'] ?? now()->addDay()->toDateString(),
                        'reference_type' => ProductionOrder::class,
                        'reference_id' => $order->id,
                        'reference_no' => $order->number,
                    ]);
                }
                $order->update([
                    'produced_qty' => DB::raw('produced_qty + '.$produced),
                    'rejected_qty' => DB::raw('rejected_qty + '.((float) ($data['rejected_qty'] ?? 0))),
                    'rework_qty' => DB::raw('rework_qty + '.((float) ($data['rework_qty'] ?? 0))),
                    'shift_id' => $data['shift_id'] ?? $order->shift_id,
                    'status' => ((float) $order->produced_qty + $produced) >= ((float) $order->planned_qty) ? 'COMPLETED' : 'PARTIAL',
                    'completed_at' => now(),
                ]);
                $costing->forProductionOrder($order->fresh(), [
                    'labor_cost' => $data['labor_cost'] ?? 0,
                    'overhead_cost' => $data['overhead_cost'] ?? 0,
                ]);
            });
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal menyelesaikan produksi: '.$e->getMessage());
        }
        event(new ProductionCompleted($order->fresh()));

        return back()->with('success', 'Hasil produksi masuk stok + costing dihitung.');
    }

    public function cancel(ProductionOrder $order)
    {
        $this->ensureOrgAccess($order);
        abort_unless(in_array($order->status, ['PLANNED', 'RELEASED']), 422, 'Order yang sudah berjalan tidak dapat dibatalkan.');
        $order->update(['status' => 'CANCELLED']);

        return back()->with('success', 'Order dibatalkan.');
    }
}
