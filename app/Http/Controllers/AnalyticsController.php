<?php

namespace App\Http\Controllers;

use App\Models\Costing;
use App\Models\Delivery;
use App\Models\InventoryMovement;
use App\Models\ProductionOrder;
use App\Models\School;
use App\Models\Waste;
use App\Services\AiAdvisorInterface;
use App\Services\KpiService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AnalyticsController extends Controller
{
    public function executive(Request $request, KpiService $kpiService, AiAdvisorInterface $advisor)
    {
        $orgId = $request->user()->organization_id;
        $kitchenId = $request->user()->central_kitchen_id;
        [$from, $to] = $kpiService->period($request->get('preset', 'custom'), $request->get('from', now()->subDays(30)->toDateString()), $request->get('to', now()->toDateString()));
        $compared = $kpiService->compare($orgId, $kitchenId, $from, $to);
        $kpi = array_map(fn ($r) => $r['value'], $compared);
        $kpi['procurement'] = $kpi['procurement_spend'];
        $kpi['production_cost'] = $kpi['production_cost'] ?? 0;
        $kpi['schools_served'] = School::where('organization_id', $orgId)->active()->count();

        $trend = ProductionOrder::where('organization_id', $orgId)->when($kitchenId, fn ($q) => $q->where('central_kitchen_id', $kitchenId))
            ->whereBetween('production_date', [$from, $to])
            ->groupBy('production_date')->selectRaw('production_date, SUM(produced_qty) as qty, SUM(rejected_qty) as rej')
            ->orderBy('production_date')->get();

        $wasteByReason = Waste::where('organization_id', $orgId)->whereBetween('waste_date', [$from, $to])
            ->groupBy('reason')->selectRaw('reason, SUM(cost_loss) as loss, SUM(qty) as qty')->orderByDesc('loss')->get();

        $advisories = [
            'waste' => $advisor->wasteAnalysis((int) ($kitchenId ?? 0))->toArray(),
        ];

        return view('analytics.executive', compact('kpi', 'compared', 'trend', 'wasteByReason', 'advisories', 'from', 'to'));
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
