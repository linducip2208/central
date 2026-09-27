<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class NumberService
{
    public const PREFIXES = [
        'PR' => 'purchase_requests', 'PO' => 'purchase_orders',
        'GR' => 'goods_receipts', 'DM' => 'demands',
        'PP' => 'production_plans', 'WO' => 'production_orders',
        'QC' => 'quality_controls', 'PKG' => 'packagings',
        'DST' => 'distributions', 'DLV' => 'deliveries',
        'OPN' => 'stock_opnames', 'WST' => 'wastes',
        'MNU' => 'menus', 'RCP' => 'recipes',
    ];

    public function next(string $prefix): string
    {
        return DB::transaction(function () use ($prefix) {
            $period = now()->format('Ym');
            $counter = DB::table('document_counters')
                ->where('prefix', $prefix)
                ->where('period', $period)
                ->lockForUpdate()
                ->first();

            $next = $counter ? ((int) $counter->last_number + 1) : 1;

            DB::table('document_counters')->updateOrInsert(
                ['prefix' => $prefix, 'period' => $period],
                ['last_number' => $next, 'updated_at' => now(), 'created_at' => $counter->created_at ?? now()]
            );

            return sprintf('%s-%s-%04d', $prefix, $period, $next);
        });
    }

    public function batchNo(string $itemCode): string
    {
        return strtoupper($itemCode).'-'.now()->format('Ymd').'-'.strtoupper(substr(uniqid(), -5));
    }
}
