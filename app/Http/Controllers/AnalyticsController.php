<?php

namespace App\Http\Controllers;

use App\Models\Costing;
use App\Models\Delivery;
use App\Models\InventoryMovement;
use App\Models\ProductionOrder;
use App\Models\PurchaseOrder;
use App\Models\QualityInspection;
use App\Models\School;
use App\Models\Waste;
use App\Services\AiAdvisorInterface;
use App\Services\ForecastService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AnalyticsController extends Controller
{
    public function executive(Request $request, ForecastService $forecast, AiAdvisorInterface $advisor)
    {
        $orgId = $request->user()->organization_id;
        $kitchenId = $request->user()->central_kitchen_id;
        $from = $request->get('from', now()->subDays(30)->toDateString());
        $to = $request->get('to', now()->toDateString());

        $kpi = [
            'procurement' => (float) PurchaseOrder::where('organization_id', $orgId)->whereBetween('order_date', [$from, $to])->whereNotIn('status', ['DRAFT', 'REJECTED', 'CANCELLED'])->sum('grand_total'),
            'portions' => (int) ProductionOrder::where('organization_id', $orgId)->when($kitchenId, fn ($q) => $q->where('central_kitchen_id', $kitchenId))->whereBetween('production_date', [$from, $to])->sum('produced_qty'),
            'delivered' => (int) Delivery::where('organization_id', $orgId)->when($kitchenId, fn ($q) => $q->where('central_kitchen_id', $kitchenId))->whereBetween('delivery_date', [$from, $to])->sum('qty_delivered'),
            'planned_delivery' => (int) Delivery::where('organization_id', $orgId)->when($kitchenId, fn ($q) => $q->where('central_kitchen_id', $kitchenId))->whereBetween('delivery_date', [$from, $to])->sum('qty_planned'),
            'waste_loss' => (float) Waste::where('organization_id', $orgId)->whereBetween('waste_date', [$from, $to])->sum('cost_loss'),
            'production_cost' => (float) Costing::where('organization_id', $orgId)->whereBetween('costing_date', [$from, $to])->sum('total_cost'),
            'qc_fail_rate' => 0,
            'schools_served' => School::where('organization_id', $orgId)->active()->count(),
        ];
        $qcTotal = QualityInspection::where('organization_id', $orgId)->whereBetween('inspection_date', [$from, $to])->count();
        $qcFail = QualityInspection::where('organization_id', $orgId)->whereBetween('inspection_date', [$from, $to])->where('result', 'FAILED')->count();
        $kpi['qc_fail_rate'] = $qcTotal > 0 ? round($qcFail / $qcTotal * 100, 1) : 0;
        $kpi['service_level'] = $kpi['planned_delivery'] > 0 ? round($kpi['delivered'] / $kpi['planned_delivery'] * 100, 1) : 0;
        $kpi['cost_per_portion'] = $kpi['portions'] > 0 ? round(($kpi['production_cost'] + $kpi['waste_loss']) / $kpi['portions'], 2) : 0;

        $trend = ProductionOrder::where('organization_id', $orgId)->when($kitchenId, fn ($q) => $q->where('central_kitchen_id', $kitchenId))
            ->whereBetween('production_date', [$from, $to])
            ->groupBy('production_date')->selectRaw('production_date, SUM(produced_qty) as qty, SUM(rejected_qty) as rej')
            ->orderBy('production_date')->get();

        $wasteByReason = Waste::where('organization_id', $orgId)->whereBetween('waste_date', [$from, $to])
            ->groupBy('reason')->selectRaw('reason, SUM(cost_loss) as loss, SUM(qty) as qty')->orderByDesc('loss')->get();

        $advisories = [
            'waste' => $advisor->wasteAnalysis((int) ($kitchenId ?? 0))->toArray(),
        ];

        return view('analytics.executive', compact('kpi', 'trend', 'wasteByReason', 'advisories', 'from', 'to'));
    }

    /** Export CSV generik (hindari dependensi spreadsheet; Excel-compatible). */
    public function export(Request $request, string $dataset): StreamedResponse
    {
        $orgId = $request->user()->organization_id;
        $map = [
            'movements' => fn () => InventoryMovement::where('organization_id', $orgId)->latest()->cursor(),
            'deliveries' => fn () => Delivery::where('organization_id', $orgId)->latest()->cursor(),
            'production' => fn () => ProductionOrder::where('organization_id', $orgId)->latest()->cursor(),
            'waste' => fn () => Waste::where('organization_id', $orgId)->latest()->cursor(),
            'costing' => fn () => Costing::where('organization_id', $orgId)->latest()->cursor(),
        ];
        abort_unless(isset($map[$dataset]), 404, 'Dataset tidak dikenal.');

        $columns = match ($dataset) {
            'movements' => ['id', 'movement_date', 'warehouse_id', 'item_type', 'item_id', 'movement_type', 'direction', 'qty', 'unit_cost', 'total_cost', 'reference_no'],
            'deliveries' => ['id', 'number', 'delivery_date', 'school_id', 'qty_planned', 'qty_delivered', 'qty_returned', 'status'],
            'production' => ['id', 'number', 'production_date', 'product_id', 'planned_qty', 'produced_qty', 'rejected_qty', 'status'],
            'waste' => ['id', 'number', 'waste_date', 'item_type', 'item_id', 'qty', 'reason', 'cost_loss'],
            'costing' => ['id', 'costing_date', 'production_order_id', 'material_cost', 'labor_cost', 'overhead_cost', 'total_cost', 'portions', 'cost_per_portion'],
        };

        return response()->streamDownload(function () use ($map, $dataset, $columns) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $columns);
            foreach ($map[$dataset]() as $row) {
                fputcsv($out, array_map(fn ($c) => $row->{$c}, $columns));
            }
            fclose($out);
        }, "mbg-{$dataset}-".now()->format('Ymd').'.csv', ['Content-Type' => 'text/csv']);
    }
}
