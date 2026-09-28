<?php

namespace App\Services;

use App\Models\Costing;
use App\Models\GoodsReceiptItem;
use App\Models\InventoryMovement;
use App\Models\Menu;
use App\Models\Product;
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

    /** Biaya standar (teoritis) dari resep/BOM × harga standar bahan. */
    public function standardForProduct(int $productId, float $portions = 1): array
    {
        $product = Product::find($productId);
        $recipe = $product?->recipes()->where('is_active', true)->latest()->first();
        $material = 0;
        $lines = [];
        if ($recipe) {
            foreach ($recipe->items as $ri) {
                $qty = $ri->requiredFor($portions, (float) $recipe->yield_qty);
                $cost = $qty * (float) ($ri->ingredient->standard_price ?? 0);
                $material += $cost;
                $lines[] = ['ingredient' => $ri->ingredient->name ?? '', 'qty' => $qty, 'price' => (float) ($ri->ingredient->standard_price ?? 0), 'cost' => $cost];
            }
        }

        return compact('material', 'lines');
    }

    /** Riwayat harga beli ingredient (PO + GR aktual). */
    public function purchaseHistory(int $ingredientId, int $limit = 20)
    {
        return GoodsReceiptItem::with(['goodsReceipt.purchaseOrder'])
            ->where('ingredient_id', $ingredientId)
            ->latest()->take($limit)->get()
            ->map(fn ($it) => [
                'date' => $it->goodsReceipt->receipt_date?->toDateString(),
                'ref' => $it->goodsReceipt->number ?? '-',
                'supplier' => $it->goodsReceipt->supplier->name ?? '-',
                'qty' => (float) $it->qty_received,
                'price' => (float) $it->unit_price,
            ]);
    }

    public function materialCostForMenu(int $menuId): float
    {
        return (float) Menu::find($menuId)?->items->sum(
            fn ($mi) => $this->standardForProduct($mi->product_id, (float) $mi->qty_per_portion)['material']
        ) ?? 0;
    }
}
