<?php

namespace App\Services;

use App\Models\ProductionOrder;
use App\Models\WorkCenter;
use Carbon\Carbon;

/**
 * Capacity planning: beban vs kapasitas per work center per hari.
 */
class CapacityService
{
    public function dailyLoad(int $centralKitchenId, string $from, string $to): array
    {
        $centers = WorkCenter::where('central_kitchen_id', $centralKitchenId)->active()->get();
        $days = [];
        $cursor = Carbon::parse($from);
        $end = Carbon::parse($to);
        while ($cursor->lte($end)) {
            $date = $cursor->toDateString();
            $planned = ProductionOrder::where('central_kitchen_id', $centralKitchenId)
                ->whereDate('production_date', $date)
                ->whereNotIn('status', ['CANCELLED'])
                ->sum('planned_qty');
            $row = ['date' => $date, 'planned' => (int) $planned, 'centers' => []];
            $totalCap = 0;
            foreach ($centers as $c) {
                // Kapasitas harian = per jam × 8 jam kerja.
                $cap = (int) $c->capacity_per_hour * 8;
                $totalCap += $cap;
                $row['centers'][] = ['name' => $c->name, 'capacity' => $cap];
            }
            $row['capacity'] = $totalCap;
            $row['utilization'] = $totalCap > 0 ? round($planned / $totalCap * 100, 1) : 0;
            $row['flag'] = $row['utilization'] > 100 ? 'OVERLOAD' : ($row['utilization'] >= 85 ? 'TIGHT' : 'OK');
            $days[] = $row;
            $cursor->addDay();
        }

        return $days;
    }
}
