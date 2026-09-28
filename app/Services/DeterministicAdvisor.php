<?php

namespace App\Services;

use App\Models\Batch;
use App\Models\Waste;

class DeterministicAdvisor implements AiAdvisorInterface
{
    public function __construct(protected ForecastService $forecast) {}

    public function demandForecast(int $productId, int $horizonDays = 14): AdvisoryResult
    {
        $daily = $this->forecast->forecastProductDemand($productId, 14);
        $projected = round($daily * $horizonDays);

        return new AdvisoryResult(
            'demand_forecast',
            "Proyeksi kebutuhan produk #{$productId} {$horizonDays} hari ke depan: {$projected} porsi (rata-rata {$daily}/hari dari 14 hari terakhir).",
            [['label' => "Produk #{$productId}", 'value' => $projected, 'reason' => "moving-average 14 hari × {$horizonDays} hari"]],
        );
    }

    public function expiryRisk(int $warehouseId): AdvisoryResult
    {
        $batches = Batch::available()->where('warehouse_id', $warehouseId)
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<=', now()->addDays(14))
            ->orderBy('expiry_date')->get();
        $items = $batches->map(fn ($b) => [
            'label' => $b->batch_no, 'value' => (float) $b->remaining_qty,
            'reason' => 'expired '.$b->expiry_date?->toDateString(),
        ])->toArray();

        return new AdvisoryResult(
            'expiry_risk', count($items).' batch berisiko expired ≤14 hari di gudang #'.$warehouseId.'.', $items,
        );
    }

    public function wasteAnalysis(int $centralKitchenId, int $days = 30): AdvisoryResult
    {
        $since = now()->subDays($days)->toDateString();
        $rows = Waste::where('central_kitchen_id', $centralKitchenId)
            ->whereDate('waste_date', '>=', $since)
            ->selectRaw('reason, SUM(qty) as qty, SUM(cost_loss) as loss')
            ->groupBy('reason')->orderByDesc('loss')->get();
        $items = $rows->map(fn ($r) => [
            'label' => $r->reason, 'value' => (float) $r->loss,
            'reason' => number_format((float) $r->qty, 2).' unit terbuang dalam '.$days.' hari',
        ])->toArray();

        return new AdvisoryResult(
            'waste_analysis',
            $rows->isEmpty() ? 'Tidak ada waste tercatat.' : 'Penyebab rugi terbesar: '.$rows->first()->reason.' ('.mbg_currency((float) $rows->first()->loss).').',
            $items,
        );
    }
}
