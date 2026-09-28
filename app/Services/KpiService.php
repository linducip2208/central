<?php

namespace App\Services;

use App\Models\Batch;
use App\Models\CapaAction;
use App\Models\Costing;
use App\Models\Delivery;
use App\Models\Ingredient;
use App\Models\InventoryStock;
use App\Models\NonConformance;
use App\Models\ProductionOrder;
use App\Models\PurchaseOrder;
use App\Models\QualityInspection;
use App\Models\SchoolConfirmation;
use App\Models\Warehouse;
use App\Models\Waste;
use Carbon\Carbon;

/**
 * Centralized KPI definitions (single source — controllers/views must not
 * duplicate these calculations). Supports period comparison.
 */
class KpiService
{
    public function __construct(protected ForecastService $forecast) {}

    public function period(string $preset, ?string $from = null, ?string $to = null): array
    {
        return match ($preset) {
            'today' => [now()->toDateString(), now()->toDateString()],
            'yesterday' => [now()->subDay()->toDateString(), now()->subDay()->toDateString()],
            'week' => [now()->startOfWeek()->toDateString(), now()->endOfWeek()->toDateString()],
            'month' => [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()],
            'quarter' => [now()->firstOfQuarter()->toDateString(), now()->lastOfQuarter()->toDateString()],
            'year' => [now()->startOfYear()->toDateString(), now()->endOfYear()->toDateString()],
            default => [$from ?? now()->subDays(30)->toDateString(), $to ?? now()->toDateString()],
        };
    }

    public function previous(string $from, string $to): array
    {
        $days = Carbon::parse($from)->diffInDays(Carbon::parse($to)) + 1;

        return [Carbon::parse($from)->subDays($days)->toDateString(), Carbon::parse($from)->subDay()->toDateString()];
    }

    protected function scope($query, int $orgId, ?int $kitchenId)
    {
        return $query->where('organization_id', $orgId)->when($kitchenId, fn ($q) => $q->where('central_kitchen_id', $kitchenId));
    }

    public function summary(int $orgId, ?int $kitchenId, string $from, string $to): array
    {
        $proc = (clone $this->scope(PurchaseOrder::query(), $orgId, $kitchenId))->whereBetween('order_date', [$from, $to]);
        $prod = (clone $this->scope(ProductionOrder::query(), $orgId, $kitchenId))->whereBetween('production_date', [$from, $to]);
        $dlv = (clone $this->scope(Delivery::query(), $orgId, $kitchenId))->whereBetween('delivery_date', [$from, $to]);
        $waste = Waste::where('organization_id', $orgId)->when($kitchenId, fn ($q) => $q->where('central_kitchen_id', $kitchenId))->whereBetween('waste_date', [$from, $to]);
        $cost = Costing::where('organization_id', $orgId)->when($kitchenId, fn ($q) => $q->where('central_kitchen_id', $kitchenId))->whereBetween('costing_date', [$from, $to]);
        $qc = QualityInspection::where('organization_id', $orgId)->when($kitchenId, fn ($q) => $q->where('central_kitchen_id', $kitchenId))->whereBetween('inspection_date', [$from, $to]);

        $portions = (int) (clone $prod)->sum('produced_qty');
        $planned = (int) (clone $dlv)->sum('qty_planned');
        $delivered = (int) (clone $dlv)->sum('qty_delivered');
        $qcTotal = (clone $qc)->count();
        $qcFail = (clone $qc)->where('result', 'FAILED')->count();
        $prodCost = (float) (clone $cost)->sum('total_cost');
        $wasteLoss = (float) (clone $waste)->sum('cost_loss');

        return [
            'procurement_spend' => (float) (clone $proc)->whereNotIn('status', ['DRAFT', 'REJECTED', 'CANCELLED'])->sum('grand_total'),
            'po_count' => (clone $proc)->count(),
            'portions' => $portions,
            'service_level' => $planned > 0 ? round($delivered / $planned * 100, 1) : 0,
            'delivered' => $delivered,
            'planned_delivery' => $planned,
            'waste_loss' => $wasteLoss,
            'waste_count' => (clone $waste)->count(),
            'production_cost' => $prodCost,
            'cost_per_portion' => $portions > 0 ? round(($prodCost + $wasteLoss) / $portions, 2) : 0,
            'qc_total' => $qcTotal,
            'qc_fail_rate' => $qcTotal > 0 ? round($qcFail / $qcTotal * 100, 1) : 0,
            'open_ncr' => NonConformance::where('organization_id', $orgId)->when($kitchenId, fn ($q) => $q->where('central_kitchen_id', $kitchenId))->where('status', 'OPEN')->count(),
            'complaints' => SchoolConfirmation::whereNotNull('complaint')->where('complaint', '!=', '')->whereHas('school', fn ($q) => $q->where('organization_id', $orgId))->count(),
        ];
    }

    public function compare(int $orgId, ?int $kitchenId, string $from, string $to): array
    {
        [$pFrom, $pTo] = $this->previous($from, $to);
        $cur = $this->summary($orgId, $kitchenId, $from, $to);
        $prev = $this->summary($orgId, $kitchenId, $pFrom, $pTo);
        $out = [];
        foreach ($cur as $k => $v) {
            $p = $prev[$k] ?? 0;
            $out[$k] = ['value' => $v, 'prev' => $p, 'delta' => round($v - $p, 2)];
        }

        return $out;
    }

    public function controlTower(int $orgId, ?int $kitchenId): array
    {
        $today = now()->toDateString();
        $warehouses = Warehouse::when($kitchenId, fn ($q) => $q->where('central_kitchen_id', $kitchenId))->pluck('id');
        $lowStock = Ingredient::active()->where('organization_id', $orgId)->count()
            ? $this->lowStockCount($orgId, $warehouses) : 0;

        return [
            'production_today' => (int) $this->scope(ProductionOrder::query(), $orgId, $kitchenId)->whereDate('production_date', $today)->sum('produced_qty'),
            'pending_production' => (clone $this->scope(ProductionOrder::query(), $orgId, $kitchenId))->whereIn('status', ['PLANNED', 'RELEASED'])->count(),
            'stock_risk' => $lowStock,
            'expiry_risk' => Batch::available()->whereIn('warehouse_id', $warehouses)->whereNotNull('expiry_date')->whereDate('expiry_date', '<=', now()->addDays(14))->count(),
            'procurement_pending' => (clone $this->scope(PurchaseOrder::query(), $orgId, $kitchenId))->whereIn('status', ['SUBMITTED', 'DRAFT'])->count(),
            'qc_open_ncr' => NonConformance::where('organization_id', $orgId)->when($kitchenId, fn ($q) => $q->where('central_kitchen_id', $kitchenId))->where('status', 'OPEN')->count(),
            'capa_overdue' => CapaAction::where('status', '!=', 'DONE')->whereDate('due_date', '<', $today)->whereHas('nonConformance', fn ($q) => $q->where('organization_id', $orgId))->count(),
            'deliveries_active' => (clone $this->scope(Delivery::query(), $orgId, $kitchenId))->whereDate('delivery_date', $today)->whereIn('status', ['PLANNED', 'IN_TRANSIT'])->count(),
            'deliveries_failed' => (clone $this->scope(Delivery::query(), $orgId, $kitchenId))->whereDate('delivery_date', $today)->where('status', 'FAILED')->count(),
            'complaints_open' => SchoolConfirmation::whereNotNull('complaint')->where('complaint', '!=', '')->whereHas('school', fn ($q) => $q->where('organization_id', $orgId))->count(),
            'waste_today' => (float) Waste::where('organization_id', $orgId)->when($kitchenId, fn ($q) => $q->where('central_kitchen_id', $kitchenId))->whereDate('waste_date', $today)->sum('cost_loss'),
        ];
    }

    protected function lowStockCount(int $orgId, $warehouseIds): int
    {
        $map = InventoryStock::whereIn('warehouse_id', $warehouseIds)->where('item_type', 'ingredient')
            ->groupBy('item_id')->selectRaw('item_id, SUM(qty) as total')->pluck('total', 'item_id');
        $n = 0;
        foreach (Ingredient::active()->where('organization_id', $orgId)->cursor() as $ing) {
            if ((float) ($map[$ing->id] ?? 0) <= (float) $ing->min_stock) {
                $n++;
            }
        }

        return $n;
    }
}
