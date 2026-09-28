<?php

namespace App\Services;

use App\Models\Batch;
use App\Models\Delivery;
use App\Models\DeliveryItem;
use App\Models\GoodsReceipt;
use App\Models\InventoryMovement;
use App\Models\ProductionOrder;

/**
 * Batch genealogy dua arah berbasis ledger + referensi dokumen.
 * Forward: supplier batch → GR → inventory → produksi → finished batch
 *          → packaging → delivery → sekolah.
 * Backward: finished batch → WO → consume movements → ingredient batch → GR → supplier.
 */
class TraceabilityService
{
    public function forward(Batch $batch): array
    {
        $chain = ['batch' => $this->batchNode($batch), 'goods_receipt' => null, 'productions' => [], 'deliveries' => []];

        if ($batch->source_type === 'PURCHASE' && $batch->source_id) {
            $gr = GoodsReceipt::with('supplier')->find($batch->source_id);
            $chain['goods_receipt'] = $gr ? ['number' => $gr->number, 'date' => $gr->receipt_date?->toDateString(), 'supplier' => $gr->supplier->name ?? '-'] : null;
        }

        if ($batch->item_type === 'ingredient') {
            // WO yang mengonsumsi batch ini.
            $consumes = InventoryMovement::where('movement_type', 'PRODUCTION_CONSUMPTION')
                ->where('batch_id', $batch->id)->get();
            $woIds = $consumes->pluck('reference_id')->unique()->filter();
            foreach (ProductionOrder::whereIn('id', $woIds)->with('product')->get() as $wo) {
                $node = ['number' => $wo->number, 'product' => $wo->product->name ?? '-', 'status' => $wo->status, 'finished_batches' => []];
                // Finished batch dari WO ini.
                $outputs = InventoryMovement::where('movement_type', 'PRODUCTION_OUTPUT')
                    ->where('reference_type', ProductionOrder::class)->where('reference_id', $wo->id)->get();
                foreach ($outputs->pluck('batch_id')->unique()->filter() as $fbId) {
                    $fb = Batch::find($fbId);
                    if (! $fb) {
                        continue;
                    }
                    $fbNode = $this->batchNode($fb);
                    $fbNode['deliveries'] = array_values($this->deliveriesOfBatch($fb));
                    $node['finished_batches'][] = $fbNode;
                    foreach ($fbNode['deliveries'] as $d) {
                        $chain['deliveries'][$d['delivery_id']] = $d;
                    }
                }
                $chain['productions'][] = $node;
            }
        } else {
            $chain['deliveries'] = array_values($this->deliveriesOfBatch($batch));
        }

        $chain['deliveries'] = array_values($chain['deliveries']);

        return $chain;
    }

    public function backward(Batch $batch): array
    {
        $chain = ['batch' => $this->batchNode($batch), 'production' => null, 'ingredient_batches' => []];

        if ($batch->item_type === 'product' && $batch->source_type === 'PRODUCTION' && $batch->source_id) {
            $wo = ProductionOrder::with('product')->find($batch->source_id);
            if ($wo) {
                $chain['production'] = ['number' => $wo->number, 'product' => $wo->product->name ?? '-', 'date' => $wo->production_date?->toDateString()];
                $consumes = InventoryMovement::where('movement_type', 'PRODUCTION_CONSUMPTION')
                    ->where('reference_type', ProductionOrder::class)->where('reference_id', $wo->id)->get();
                foreach ($consumes->pluck('batch_id')->unique()->filter() as $ibId) {
                    $ib = Batch::with('supplier')->find($ibId);
                    if (! $ib) {
                        continue;
                    }
                    $node = $this->batchNode($ib);
                    if ($ib->source_type === 'PURCHASE' && $ib->source_id) {
                        $gr = GoodsReceipt::with('supplier')->find($ib->source_id);
                        $node['goods_receipt'] = $gr ? ['number' => $gr->number, 'supplier' => $gr->supplier->name ?? '-'] : null;
                    }
                    $chain['ingredient_batches'][] = $node;
                }
            }
        } elseif ($batch->source_type === 'PURCHASE' && $batch->source_id) {
            $gr = GoodsReceipt::with('supplier')->find($batch->source_id);
            $chain['goods_receipt'] = $gr ? ['number' => $gr->number, 'supplier' => $gr->supplier->name ?? '-'] : null;
        }

        return $chain;
    }

    protected function deliveriesOfBatch(Batch $batch): array
    {
        $out = [];
        $movs = InventoryMovement::whereIn('movement_type', ['DELIVERY'])
            ->where('batch_id', $batch->id)->get();
        $deliveryIds = $movs->pluck('reference_id')->unique()->filter();
        foreach (Delivery::whereIn('id', $deliveryIds)->with('school')->get() as $d) {
            $out[$d->id] = [
                'delivery_id' => $d->id, 'number' => $d->number,
                'school' => $d->school->name ?? '-', 'status' => $d->status,
                'date' => $d->delivery_date?->toDateString(),
            ];
        }
        // Juga via delivery_items (alur distribusi mencatat item tanpa movement batch).
        foreach (DeliveryItem::where('batch_id', $batch->id)->with('delivery.school')->get() as $di) {
            if ($di->delivery) {
                $out[$di->delivery->id] = [
                    'delivery_id' => $di->delivery->id, 'number' => $di->delivery->number,
                    'school' => $di->delivery->school->name ?? '-', 'status' => $di->delivery->status,
                    'date' => $di->delivery->delivery_date?->toDateString(),
                ];
            }
        }

        return $out;
    }

    protected function batchNode(Batch $batch): array
    {
        return [
            'id' => $batch->id, 'batch_no' => $batch->batch_no,
            'item' => $batch->item_type.' #'.$batch->item_id,
            'warehouse' => $batch->warehouse->name ?? '-',
            'remaining_qty' => (float) $batch->remaining_qty,
            'expiry' => $batch->expiry_date?->toDateString(), 'status' => $batch->status,
        ];
    }
}
