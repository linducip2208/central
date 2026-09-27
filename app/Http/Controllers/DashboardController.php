<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Delivery;
use App\Models\Ingredient;
use App\Models\InventoryMovement;
use App\Models\InventoryStock;
use App\Models\ProductionOrder;
use App\Models\PurchaseOrder;
use App\Models\School;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $kitchenId = $request->user()->central_kitchen_id;

        $warehouses = Warehouse::when($kitchenId, fn ($q) => $q->where('central_kitchen_id', $kitchenId))->pluck('id');
        // Satu query agregat untuk semua stok bahan (hindari N+1).
        $stockMap = InventoryStock::whereIn('warehouse_id', $warehouses)
            ->where('item_type', 'ingredient')
            ->groupBy('item_id')
            ->selectRaw('item_id, SUM(qty) as total')
            ->pluck('total', 'item_id');
        $lowStock = Ingredient::active()
            ->with(['unit'])
            ->get()
            ->map(fn ($ing) => ['ingredient' => $ing, 'stock' => (float) ($stockMap[$ing->id] ?? 0), 'min' => (float) $ing->min_stock])
            ->filter(fn ($r) => $r['stock'] <= $r['min'])
            ->take(8);

        $expiring = Batch::available()
            ->when($warehouses->isNotEmpty(), fn ($q) => $q->whereIn('warehouse_id', $warehouses))
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<=', now()->addDays(config('mbg.inventory.expiry_alert_days', 30)))
            ->orderBy('expiry_date')
            ->with(['warehouse'])
            ->take(8)
            ->get();

        $stats = [
            'portions_today' => ProductionOrder::when($kitchenId, fn ($q) => $q->where('central_kitchen_id', $kitchenId))->whereDate('production_date', today())->sum('produced_qty'),
            'active_pos' => PurchaseOrder::when($kitchenId, fn ($q) => $q->where('central_kitchen_id', $kitchenId))->whereIn('status', ['APPROVED', 'PARTIAL', 'SUBMITTED'])->count(),
            'in_transit' => Delivery::when($kitchenId, fn ($q) => $q->where('central_kitchen_id', $kitchenId))->whereIn('status', ['PLANNED', 'IN_TRANSIT'])->count(),
            'schools' => School::when($kitchenId, fn ($q) => $q->where('central_kitchen_id', $kitchenId))->active()->count(),
        ];

        $production7 = ProductionOrder::when($kitchenId, fn ($q) => $q->where('central_kitchen_id', $kitchenId))
            ->whereDate('production_date', '>=', now()->subDays(6))
            ->groupBy('production_date')
            ->selectRaw('production_date, SUM(produced_qty) as qty')
            ->orderBy('production_date')
            ->get();

        $recentMovements = InventoryMovement::when($warehouses->isNotEmpty(), fn ($q) => $q->whereIn('warehouse_id', $warehouses))
            ->with(['warehouse'])
            ->latest()
            ->take(10)
            ->get();

        return view('dashboard', compact('stats', 'lowStock', 'expiring', 'production7', 'recentMovements'));
    }
}
