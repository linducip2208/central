<?php

namespace App\Services;

use App\Models\Costing;
use App\Models\InventoryMovement;
use App\Models\ProductionOrder;

class CostingService
{
    /** Hitung biaya material aktual production order dari ledger konsumsi + output. */
    public function forProductionOrder(ProductionOrder $order, array $extra = []): Costing
    {
        $material = (float) InventoryMovement::where('movement_type', 'PRODUCTION_CONSUMPTION')
            ->where('reference_type', ProductionOrder::class)
            ->where('reference_id', $order->id)
            ->sum('total_cost');

        $produced = max(1, (int) $order->produced_qty);

        return Costing::updateOrCreate(
            ['production_order_id' => $order->id],
            [
                'organization_id' => $order->organization_id,
                'central_kitchen_id' => $order->central_kitchen_id,
                'menu_id' => $order->menu_id,
                'costing_date' => $order->production_date,
                'material_cost' => $material,
                'labor_cost' => $extra['labor_cost'] ?? 0,
                'overhead_cost' => $extra['overhead_cost'] ?? 0,
                'packaging_cost' => $extra['packaging_cost'] ?? 0,
                'delivery_cost' => $extra['delivery_cost'] ?? 0,
                'portions' => $produced,
                'method' => config('mbg.financial.costing_method', 'AVG'),
                'notes' => $extra['notes'] ?? null,
            ]
        );
    }

    public function materialCostForMenu(int $menuId): float
    {
        return 0; // dihitung dari agregat production orders per menu bila dibutuhkan
    }
}
