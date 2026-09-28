<?php

namespace App\Services;

use App\Models\Batch;
use App\Models\DemandPlan;
use App\Models\Ingredient;
use App\Models\InventoryStock;
use App\Models\MrpLine;
use App\Models\MrpRun;
use App\Models\PurchaseOrderItem;
use App\Models\SupplierPriceList;
use App\Models\Warehouse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Material Requirements Planning.
 * DEMAND → MENU → RECIPE/BOM → GROSS → ON-HAND → RESERVED → INCOMING PO
 * → SAFETY → NET → REKOMENDASI (explainable).
 */
class MrpService
{
    public function __construct(protected BomService $bom, protected ForecastService $forecast, protected NumberService $numbers) {}

    public function run(DemandPlan $plan, int $warehouseId, ?int $userId = null): MrpRun
    {
        return DB::transaction(function () use ($plan, $warehouseId, $userId) {
            $run = MrpRun::create([
                'organization_id' => $plan->organization_id,
                'central_kitchen_id' => $plan->central_kitchen_id,
                'demand_plan_id' => $plan->id,
                'warehouse_id' => $warehouseId,
                'number' => $this->numbers->next('MRP'),
                'run_date' => now()->toDateString(),
                'status' => 'COMPLETED',
                'created_by' => $userId ?? Auth::id(),
            ]);

            // 1. Gross requirement dari explosion per line.
            $gross = [];
            $plan->load(['lines.menu.items.product', 'lines.product']);
            foreach ($plan->lines as $line) {
                $portions = (int) $line->net_demand;
                if ($portions <= 0) {
                    continue;
                }
                if ($line->product_id) {
                    $this->addExplosion($gross, (int) $line->product_id, $portions);
                } elseif ($line->menu) {
                    foreach ($line->menu->items as $mi) {
                        $this->addExplosion($gross, (int) $mi->product_id, $portions * (float) $mi->qty_per_portion);
                    }
                }
            }

            // 2. Netting per ingredient.
            foreach ($gross as $ingId => $grossQty) {
                $ing = Ingredient::find($ingId);
                if (! $ing) {
                    continue;
                }
                $stock = InventoryStock::where('warehouse_id', $warehouseId)->where('item_type', 'ingredient')->where('item_id', $ingId);
                $onHand = (float) (clone $stock)->sum('qty');
                $reserved = (float) (clone $stock)->sum('reserved_qty');
                // Expiry-aware: stok yang kedaluwarsa dalam lead time + 7 hari tidak dihitung usable.
                $horizon = now()->addDays((int) $ing->lead_time_days + 7)->toDateString();
                $expiring = (float) Batch::available()->where('warehouse_id', $warehouseId)
                    ->where('item_type', 'ingredient')->where('item_id', $ingId)
                    ->whereNotNull('expiry_date')->whereDate('expiry_date', '<=', $horizon)->sum('remaining_qty');
                $available = max(0, $onHand - $reserved - $expiring);
                $incoming = (float) PurchaseOrderItem::where('ingredient_id', $ingId)
                    ->whereHas('purchaseOrder', fn ($q) => $q->whereIn('status', ['APPROVED', 'PARTIAL']))
                    ->selectRaw('SUM(qty_ordered - qty_received) as t')->value('t');
                $safety = max((float) $ing->safety_stock, $this->forecast->safetyStock(
                    $this->forecast->avgDailyConsumption('ingredient', $ingId), (int) $ing->lead_time_days
                ));
                $net = max(0, $grossQty - $available - $incoming + $safety);

                $moq = max((float) $ing->moq, 0);
                $multiple = max((float) $ing->order_multiple, 0);
                $suggested = $net > 0 ? max($net, $moq) : 0;
                if ($suggested > 0 && $multiple > 0) {
                    $suggested = ceil($suggested / $multiple) * $multiple; // order multiple
                }
                $price = SupplierPriceList::where('ingredient_id', $ingId)->effective()
                    ->orderBy('price')->first();
                $preferred = $ing->preferred_supplier_id
                    ? SupplierPriceList::where('ingredient_id', $ingId)->where('supplier_id', $ing->preferred_supplier_id)->effective()->first()
                    : null;
                $chosen = $preferred ?? $price;

                // Transfer antar gudang se-dapur bila ada surplus.
                $transferFrom = null;
                if ($net > 0) {
                    $siblings = Warehouse::where('central_kitchen_id', $plan->central_kitchen->id ?? 0)->where('id', '!=', $warehouseId)->pluck('id');
                    foreach ($siblings as $sibId) {
                        $sibAvail = (float) InventoryStock::where('warehouse_id', $sibId)->where('item_type', 'ingredient')->where('item_id', $ingId)->selectRaw('SUM(qty - reserved_qty) as a')->value('a');
                        $sibMax = (float) $ing->max_stock;
                        if ($sibAvail > max($net, $sibMax > 0 ? $sibMax : 0)) {
                            $transferFrom = (int) $sibId;
                            break;
                        }
                    }
                }

                $max = (float) $ing->max_stock;
                $recommendation = 'NONE';
                $explanation = "Butuh {$grossQty}; tersedia usable {$available} (stok {$onHand} − reservasi {$reserved} − hampir expired {$expiring}); incoming PO {$incoming}; safety {$safety}; net {$net}.";
                if ($max > 0 && $available > $max) {
                    $recommendation = 'SURPLUS';
                    $explanation .= " SURPLUS: tersedia melebihi max {$max} — tahan pembelian.";
                } elseif ($net > 0 && ! $chosen && ! $transferFrom) {
                    $recommendation = 'SHORTAGE';
                    $explanation .= " SHORTAGE {$net} tanpa supplier/transfer — segera cari sumber.";
                } elseif ($net > 0 && $transferFrom) {
                    $recommendation = 'TRANSFER';
                    $explanation .= " Sarankan transfer {$net} dari gudang #{$transferFrom} yang surplus.";
                } elseif ($net > 0) {
                    $recommendation = 'PURCHASE';
                    $explanation .= " Sarankan beli {$suggested}".($moq > 0 ? " (MOQ {$moq})" : '').($multiple > 0 ? " (kelipatan {$multiple})" : '').($chosen ? " dari {$chosen->supplier->name} @ ".mbg_currency((float) $chosen->price) : ' (belum ada price list)').'.';
                }
                if ($expiring > 0) {
                    $explanation .= " Perhatian: {$expiring} akan expired ≤ ".((int) $ing->lead_time_days + 7).' hari.';
                }

                MrpLine::create([
                    'mrp_run_id' => $run->id, 'ingredient_id' => $ingId,
                    'gross_requirement' => $grossQty, 'on_hand' => $onHand, 'reserved' => $reserved,
                    'available' => $available, 'incoming' => $incoming, 'safety_stock' => $safety,
                    'expiring_soon' => $expiring,
                    'net_requirement' => $net, 'suggested_order_qty' => $suggested,
                    'suggested_supplier_id' => $chosen?->supplier_id, 'suggested_price' => $chosen?->price ?? 0,
                    'transfer_from_warehouse_id' => $transferFrom,
                    'recommendation' => $recommendation, 'explanation' => $explanation,
                ]);
            }

            return $run->fresh();
        });
    }

    protected function addExplosion(array &$gross, int $productId, float $portions): void
    {
        foreach ($this->bom->explode($productId, $portions) as $need) {
            $gross[$need['ingredient_id']] = ($gross[$need['ingredient_id']] ?? 0) + $need['qty'];
        }
    }
}
