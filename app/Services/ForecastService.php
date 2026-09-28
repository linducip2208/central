<?php

namespace App\Services;

use App\Models\Delivery;
use App\Models\DeliveryItem;
use App\Models\Ingredient;
use App\Models\InventoryMovement;
use App\Models\InventoryStock;

/**
 * Deterministic forecasting (moving average) + safety stock.
 * Antarmuka ini juga menjadi seam AI-ready: model/LLM hanya boleh
 * memberi rekomendasi lewat ForecastResult yang explainable — tidak
 * pernah memutasi stok langsung (lihat AiAdvisorInterface).
 */
class ForecastService
{
    /** Rata-rata konsumsi harian ingredient N hari terakhir dari ledger. */
    public function avgDailyConsumption(string $itemType, int $itemId, int $days = 30): float
    {
        $since = now()->subDays($days)->toDateString();
        $out = (float) InventoryMovement::where('item_type', $itemType)->where('item_id', $itemId)
            ->where('direction', 'OUT')
            ->whereIn('movement_type', ['PRODUCTION_CONSUMPTION', 'WASTE', 'DELIVERY'])
            ->whereDate('movement_date', '>=', $since)
            ->sum('qty');

        return $days > 0 ? $out / $days : 0;
    }

    /** Safety stock = avg_daily × lead_time × service_factor (default 1.5 ≈ cakupan variabilitas). */
    public function safetyStock(float $avgDaily, int $leadTimeDays, float $serviceFactor = 1.5): float
    {
        return round($avgDaily * max(0, $leadTimeDays) * $serviceFactor, 3);
    }

    public function daysOfStock(float $onHand, float $avgDaily): ?float
    {
        if ($avgDaily <= 0) {
            return null;
        }

        return round($onHand / $avgDaily, 1);
    }

    /** Proyeksi demand produk dari delivery N hari terakhir (moving average). */
    public function forecastProductDemand(int $productId, int $days = 14): float
    {
        $since = now()->subDays($days)->toDateString();
        $delivered = (int) DeliveryItem::where('product_id', $productId)
            ->whereHas('delivery', fn ($q) => $q->whereDate('delivery_date', '>=', $since))
            ->sum('qty_delivered');

        return $days > 0 ? round($delivered / $days, 1) : 0;
    }

    public function stockoutRisk(string $itemType, int $itemId, ?int $warehouseId = null): array
    {
        $q = InventoryStock::where('item_type', $itemType)->where('item_id', $itemId);
        if ($warehouseId) {
            $q->where('warehouse_id', $warehouseId);
        }
        $onHand = (float) $q->sum('qty');
        $reserved = (float) (clone $q)->sum('reserved_qty');
        $available = max(0, $onHand - $reserved);
        $avg = $this->avgDailyConsumption($itemType, $itemId);
        $days = $this->daysOfStock($available, $avg);

        $level = 'LOW';
        if ($available <= 0) {
            $level = 'CRITICAL';
        } elseif ($days !== null && $days <= 3) {
            $level = 'HIGH';
        } elseif ($days !== null && $days <= 7) {
            $level = 'MEDIUM';
        }

        return compact('onHand', 'reserved', 'available', 'avg', 'days', 'level') + [
            'explanation' => "Tersedia {$available} dengan konsumsi rata-rata {$avg}/hari → ".($days === null ? 'belum ada histori' : "{$days} hari stok").'.',
        ];
    }
}
