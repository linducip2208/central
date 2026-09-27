<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Costing;
use App\Models\Delivery;
use App\Models\Ingredient;
use App\Models\InventoryStock;
use App\Models\Product;
use App\Models\ProductionOrder;
use App\Models\PurchaseOrder;
use App\Models\Warehouse;
use App\Models\Waste;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index()
    {
        return view('reports.index');
    }

    public function stock(Request $request)
    {
        $request->validate(['warehouse_id' => 'nullable|exists:warehouses,id']);
        $stocks = InventoryStock::with(['warehouse', 'batch'])
            ->when($request->warehouse_id, fn ($q) => $q->where('warehouse_id', $request->warehouse_id))
            ->where('qty', '>', 0)
            ->orderBy('item_type')->orderBy('item_id')
            ->get()
            ->map(function ($s) {
                $s->item_name = $s->item_type === 'product'
                    ? Product::whereKey($s->item_id)->value('name')
                    : Ingredient::whereKey($s->item_id)->value('name');
                $s->stock_value = (float) $s->qty * (float) $s->avg_cost;

                return $s;
            });
        $warehouses = Warehouse::all();
        $totalValue = $stocks->sum('stock_value');

        return view('reports.stock', compact('stocks', 'warehouses', 'totalValue'));
    }

    public function production(Request $request)
    {
        $from = $request->get('from', now()->subDays(30)->toDateString());
        $to = $request->get('to', now()->toDateString());
        $orders = ProductionOrder::with(['product'])
            ->whereBetween('production_date', [$from, $to])
            ->when($request->user()->central_kitchen_id, fn ($q) => $q->where('central_kitchen_id', $request->user()->central_kitchen_id))
            ->orderBy('production_date')
            ->get();

        return view('reports.production', compact('orders', 'from', 'to'));
    }

    public function delivery(Request $request)
    {
        $from = $request->get('from', now()->subDays(30)->toDateString());
        $to = $request->get('to', now()->toDateString());
        $deliveries = Delivery::with(['school'])
            ->whereBetween('delivery_date', [$from, $to])
            ->when($request->user()->central_kitchen_id, fn ($q) => $q->where('central_kitchen_id', $request->user()->central_kitchen_id))
            ->orderBy('delivery_date')
            ->get();
        $fulfillment = $deliveries->sum('qty_planned') > 0
            ? round($deliveries->sum('qty_delivered') / $deliveries->sum('qty_planned') * 100, 1) : 0;

        return view('reports.delivery', compact('deliveries', 'from', 'to', 'fulfillment'));
    }

    public function financial(Request $request)
    {
        $from = $request->get('from', now()->subDays(30)->toDateString());
        $to = $request->get('to', now()->toDateString());
        $costings = Costing::whereBetween('costing_date', [$from, $to])->get();
        $purchases = PurchaseOrder::whereIn('status', ['APPROVED', 'PARTIAL', 'COMPLETED'])->whereBetween('order_date', [$from, $to])->sum('grand_total');
        $wasteLoss = Waste::whereBetween('waste_date', [$from, $to])->sum('cost_loss');

        return view('reports.financial', compact('costings', 'purchases', 'wasteLoss', 'from', 'to'));
    }

    public function expiry(Request $request)
    {
        $days = (int) ($request->get('days', 30));
        $batches = Batch::available()->with(['warehouse'])
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<=', now()->addDays($days))
            ->orderBy('expiry_date')
            ->get();

        return view('reports.expiry', compact('batches', 'days'));
    }
}
