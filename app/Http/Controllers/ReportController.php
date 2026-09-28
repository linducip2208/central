<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Costing;
use App\Models\Delivery;
use App\Models\Ingredient;
use App\Models\InventoryMovement;
use App\Models\InventoryStock;
use App\Models\Menu;
use App\Models\Product;
use App\Models\ProductionOrder;
use App\Models\PurchaseOrder;
use App\Models\QualityInspection;
use App\Models\Recall;
use App\Models\ScheduledReport;
use App\Models\Supplier;
use App\Models\SupplierInvoice;
use App\Models\Warehouse;
use App\Models\Waste;
use App\Services\ForecastService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $schedules = ScheduledReport::where('organization_id', $request->user()->organization_id)->get();

        return view('reports.index', compact('schedules'));
    }

    public function scheduleStore(Request $request)
    {
        $data = $request->validate([
            'dataset' => 'required|in:movements,deliveries,production,waste,costing',
            'frequency' => 'required|in:DAILY,WEEKLY,MONTHLY',
        ]);
        ScheduledReport::firstOrCreate(
            ['organization_id' => $request->user()->organization_id, 'dataset' => $data['dataset']],
            $data + ['is_active' => true, 'created_by' => $request->user()->id]
        );

        return back()->with('success', 'Jadwal laporan dibuat. Dijalankan via `php artisan mbg:run-scheduled-reports`.');
    }

    public function scheduleToggle(ScheduledReport $schedule)
    {
        abort_unless((int) $schedule->organization_id === (int) request()->user()->organization_id, 403);
        $schedule->update(['is_active' => ! $schedule->is_active]);

        return back()->with('success', 'Jadwal diperbarui.');
    }

    /** Intelligence: risiko expired, risiko stockout, excess, aging, turnover. */
    public function intelligence(Request $request, ForecastService $forecast)
    {
        $orgId = $request->user()->organization_id;
        $whIds = Warehouse::when($request->user()->central_kitchen_id, fn ($q) => $q->where('central_kitchen_id', $request->user()->central_kitchen_id))->pluck('id');

        $expiryRisk = Batch::available()->whereIn('warehouse_id', $whIds)
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<=', now()->addDays(30))
            ->with('warehouse')->orderBy('expiry_date')->take(20)->get();

        $stockMap = InventoryStock::whereIn('warehouse_id', $whIds)->where('item_type', 'ingredient')
            ->groupBy('item_id')->selectRaw('item_id, SUM(qty) as q, SUM(reserved_qty) as r')->get()->keyBy('item_id');
        $stockout = [];
        $excess = [];
        $turnover = [];
        foreach (Ingredient::active()->where('organization_id', $orgId)->with('unit')->cursor() as $ing) {
            $row = $stockMap[$ing->id] ?? null;
            $onHand = (float) ($row->q ?? 0);
            $available = max(0, $onHand - (float) ($row->r ?? 0));
            $avg = $forecast->avgDailyConsumption('ingredient', $ing->id);
            $days = $forecast->daysOfStock($available, $avg);
            $max = (float) $ing->max_stock;
            if ($available <= (float) $ing->min_stock) {
                $stockout[] = ['name' => $ing->name, 'available' => $available, 'min' => (float) $ing->min_stock, 'days' => $days];
            }
            if ($max > 0 && $available > $max) {
                $excess[] = ['name' => $ing->name, 'available' => $available, 'max' => $max];
            }
            $turnover[] = ['name' => $ing->name, 'on_hand' => $onHand, 'avg_daily' => $avg, 'days' => $days];
        }
        usort($turnover, fn ($a, $b) => ($a['days'] ?? 9999) <=> ($b['days'] ?? 9999));

        // Dead stock: tidak ada movement OUT 60 hari + stok > 0.
        $since = now()->subDays(60)->toDateString();
        $movedIds = InventoryMovement::where('direction', 'OUT')->whereDate('movement_date', '>=', $since)->distinct()->pluck('item_id');
        $deadStock = Ingredient::active()->where('organization_id', $orgId)
            ->whereNotIn('id', $movedIds)
            ->whereIn('id', $stockMap->filter(fn ($r) => (float) $r->q > 0)->keys())
            ->take(20)->get();

        return view('reports.intelligence', compact('expiryRisk', 'stockout', 'excess', 'turnover', 'deadStock'));
    }

    public function waste(Request $request)
    {
        $from = $request->get('from', now()->subDays(30)->toDateString());
        $to = $request->get('to', now()->toDateString());
        $rows = Waste::where('organization_id', $request->user()->organization_id)
            ->whereBetween('waste_date', [$from, $to])
            ->selectRaw('reason, COUNT(*) as n, SUM(qty) as qty, SUM(cost_loss) as loss')
            ->groupBy('reason')->orderByDesc('loss')->get();
        $total = $rows->sum('loss');
        $detail = Waste::where('organization_id', $request->user()->organization_id)
            ->whereBetween('waste_date', [$from, $to])->latest('waste_date')->take(50)->get();

        return view('reports.waste', compact('rows', 'total', 'detail', 'from', 'to'));
    }

    public function supplier(Request $request)
    {
        $from = $request->get('from', now()->subDays(90)->toDateString());
        $to = $request->get('to', now()->toDateString());
        $suppliers = Supplier::where('organization_id', $request->user()->organization_id)->get()->map(function ($s) use ($from, $to) {
            $pos = PurchaseOrder::where('supplier_id', $s->id)->whereBetween('order_date', [$from, $to]);
            $onTime = (int) (clone $pos)->whereColumn('expected_date', '>=', 'order_date')->count();
            $total = (int) (clone $pos)->count();
            $qcFail = QualityInspection::where('organization_id', $s->organization_id)
                ->whereBetween('inspection_date', [$from, $to])->where('result', 'FAILED')->count();

            return [
                'name' => $s->name, 'code' => $s->code, 'rating' => $s->rating,
                'po_count' => $total,
                'on_time_pct' => $total > 0 ? round($onTime / $total * 100, 1) : null,
                'spend' => (float) (clone $pos)->sum('grand_total'),
                'qc_failed' => $qcFail,
            ];
        });

        return view('reports.supplier', compact('suppliers', 'from', 'to'));
    }

    public function recall(Request $request)
    {
        $recalls = Recall::where('organization_id', $request->user()->organization_id)->withCount('items')->latest()->take(30)->get();

        return view('reports.recall', compact('recalls'));
    }

    public function apAging(Request $request)
    {
        $invoices = SupplierInvoice::with(['supplier'])
            ->where('organization_id', $request->user()->organization_id)
            ->where('payment_status', 'UNPAID')
            ->whereNotNull('due_date')
            ->orderBy('due_date')->get()
            ->map(function ($inv) {
                $overdue = now()->startOfDay()->gt($inv->due_date);
                $bucket = $overdue ? ($inv->due_date->diffInDays(now()).' hari lewat') : ($inv->due_date->diffInDays(now()).' hari lagi');
                $inv->bucket = $bucket;
                $inv->is_overdue = $overdue;

                return $inv;
            });
        $total = $invoices->sum('grand_total');
        $overdueTotal = $invoices->where('is_overdue', true)->sum('grand_total');

        return view('reports.ap-aging', compact('invoices', 'total', 'overdueTotal'));
    }

    public function schoolCost(Request $request)
    {
        $from = $request->get('from', now()->subDays(30)->toDateString());
        $to = $request->get('to', now()->toDateString());
        // Alokasi: porsi terkirim per sekolah × biaya rata-rata per porsi periode.
        $avgCost = (float) Costing::where('organization_id', $request->user()->organization_id)
            ->whereBetween('costing_date', [$from, $to])->avg('cost_per_portion');
        $rows = Delivery::with(['school'])
            ->where('organization_id', $request->user()->organization_id)
            ->whereBetween('delivery_date', [$from, $to])
            ->selectRaw('school_id, SUM(qty_delivered) as portions, SUM(qty_returned) as returned')
            ->groupBy('school_id')->get()
            ->map(fn ($r) => ['school' => $r->school->name ?? '-', 'portions' => (int) $r->portions, 'returned' => (int) $r->returned, 'cost' => (int) $r->portions * $avgCost])
            ->sortByDesc('cost')->values();

        return view('reports.school-cost', compact('rows', 'from', 'to', 'avgCost'));
    }

    public function nutrition(Request $request)
    {
        $menus = Menu::where('organization_id', $request->user()->organization_id)->with('nutrition')->latest('menu_date')->take(14)->get();
        $targets = ['calories' => 600, 'protein_g' => 20, 'carbs_g' => 80, 'fat_g' => 18];

        return view('reports.nutrition', compact('menus', 'targets'));
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
