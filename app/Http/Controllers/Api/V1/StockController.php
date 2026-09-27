<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\Ingredient;
use App\Models\InventoryMovement;
use App\Models\InventoryStock;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class StockController extends Controller
{
    protected function warehouses(Request $request)
    {
        return Warehouse::when($request->user()->central_kitchen_id, fn ($q) => $q->where('central_kitchen_id', $request->user()->central_kitchen_id))->pluck('id');
    }

    protected function itemName(string $type, int $id): ?string
    {
        return $type === 'product'
            ? Product::whereKey($id)->value('name')
            : Ingredient::whereKey($id)->value('name');
    }

    /** Ringkasan stok per item (read-only, org/kitchen scoped). */
    public function summary(Request $request)
    {
        $whIds = $this->warehouses($request);
        $rows = InventoryStock::whereIn('warehouse_id', $whIds)
            ->when($request->filled('warehouse_id'), fn ($q) => $q->where('warehouse_id', $request->warehouse_id))
            ->where('qty', '>', 0)
            ->selectRaw('item_type, item_id, SUM(qty) as qty, SUM(reserved_qty) as reserved')
            ->groupBy('item_type', 'item_id')
            ->paginate(50);

        $rows->getCollection()->transform(fn ($r) => [
            'item_type' => $r->item_type, 'item_id' => $r->item_id,
            'name' => $this->itemName($r->item_type, $r->item_id),
            'qty' => (float) $r->qty, 'reserved' => (float) $r->reserved,
            'available' => max(0, (float) $r->qty - (float) $r->reserved),
        ]);

        return response()->json($rows);
    }

    public function lowStock(Request $request)
    {
        $whIds = $this->warehouses($request);
        $map = InventoryStock::whereIn('warehouse_id', $whIds)->where('item_type', 'ingredient')
            ->groupBy('item_id')->selectRaw('item_id, SUM(qty) as total')->pluck('total', 'item_id');

        $low = Ingredient::active()->where('organization_id', $request->user()->organization_id)->get()
            ->map(fn ($ing) => ['id' => $ing->id, 'code' => $ing->code, 'name' => $ing->name, 'stock' => (float) ($map[$ing->id] ?? 0), 'min_stock' => (float) $ing->min_stock, 'unit' => $ing->unit->symbol ?? null])
            ->filter(fn ($r) => $r['stock'] <= $r['min_stock'])->values();

        return response()->json(['data' => $low]);
    }

    public function expiring(Request $request)
    {
        $request->validate(['days' => 'nullable|integer|min:1|max:365']);
        $days = (int) $request->get('days', 30);
        $whIds = $this->warehouses($request);

        $batches = Batch::available()->whereIn('warehouse_id', $whIds)
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<=', now()->addDays($days))
            ->orderBy('expiry_date')
            ->with('warehouse')
            ->paginate(50);

        $batches->getCollection()->transform(fn ($b) => [
            'id' => $b->id, 'batch_no' => $b->batch_no, 'item' => $this->itemName($b->item_type, $b->item_id),
            'warehouse' => $b->warehouse->name ?? '-', 'expiry_date' => $b->expiry_date?->toDateString(),
            'remaining_qty' => (float) $b->remaining_qty,
        ]);

        return response()->json($batches);
    }

    public function movements(Request $request)
    {
        $whIds = $this->warehouses($request);
        $movs = InventoryMovement::whereIn('warehouse_id', $whIds)
            ->when($request->filled('type'), fn ($q) => $q->where('movement_type', $request->type))
            ->latest()->paginate(50);

        return response()->json($movs);
    }
}
