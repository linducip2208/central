<?php

namespace App\Services;

use App\Models\Batch;
use App\Models\InventoryMovement;
use App\Models\InventoryStock;
use App\Models\Warehouse;
use App\Services\Exceptions\InsufficientStockException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Inventory ledger engine.
 *
 * Semua mutasi stok WAJIB lewat service ini. Setiap mutasi:
 *  - berjalan dalam DB transaction
 *  - mengunci baris inventory_stocks (lockForUpdate) agar konsisten di bawah konkurensi
 *  - menulis satu baris ledger inventory_movements (tidak pernah update/delete)
 *  - memperbarui agregat inventory_stocks + batches.remaining_qty
 */
class InventoryService
{
    public function __construct(protected NumberService $numbers) {}

    // ------------------------------------------------------------------
    // Query helpers
    // ------------------------------------------------------------------

    public function stockOf(int $warehouseId, string $itemType, int $itemId): float
    {
        return (float) InventoryStock::where('warehouse_id', $warehouseId)
            ->where('item_type', $itemType)
            ->where('item_id', $itemId)
            ->sum('qty');
    }

    public function availableOf(int $warehouseId, string $itemType, int $itemId): float
    {
        $row = InventoryStock::where('warehouse_id', $warehouseId)
            ->where('item_type', $itemType)
            ->where('item_id', $itemId)
            ->selectRaw('COALESCE(SUM(qty),0) as q, COALESCE(SUM(reserved_qty),0) as r')
            ->first();

        return max(0, (float) ($row->q ?? 0) - (float) ($row->r ?? 0));
    }

    /**
     * Kandidat batch alokasi: AVAILABLE, qty > 0, belum kedaluwarsa.
     * Urutan mengikuti metode gudang: FEFO (expiry tercepat, NULL terakhir)
     * atau FIFO (batch dibuat lebih dulu).
     */
    public function fefoBatches(int $warehouseId, string $itemType, int $itemId, ?int $excludeBatchId = null)
    {
        $method = Warehouse::whereKey($warehouseId)->value('fifo_method') ?? 'FEFO';
        $q = Batch::available()
            ->where('warehouse_id', $warehouseId)
            ->where('item_type', $itemType)
            ->where('item_id', $itemId)
            ->where(function ($w) {
                $w->whereNull('expiry_date')->orWhereDate('expiry_date', '>=', now()->toDateString());
            });
        if ($method === 'FIFO') {
            $q->orderBy('created_at')->orderBy('id');
        } else {
            $q->fefo();
        }
        $q->lockForUpdate();

        if ($excludeBatchId) {
            $q->where('id', '!=', $excludeBatchId);
        }

        return $q->get();
    }

    // ------------------------------------------------------------------
    // IN: purchase receipt — buat batch baru + stok + ledger
    // ------------------------------------------------------------------

    /**
     * @param  array{warehouse_id:int,item_type:string,item_id:int,qty:float,unit_id?:int,unit_cost:float,batch_no?:string,expiry_date?:string,production_date?:string,supplier_id?:int,reference_type?:string,reference_id?:int,reference_no?:string,movement_date?:string,notes?:string,organization_id?:int}  $data
     */
    public function receive(array $data): Batch
    {
        $this->guardQty($data['qty']);

        return DB::transaction(function () use ($data) {
            $this->assertNoDuplicate('PURCHASE_RECEIPT', $data);

            $batch = Batch::create([
                'organization_id' => $data['organization_id'] ?? $this->orgId(),
                'warehouse_id' => $data['warehouse_id'],
                'item_type' => $data['item_type'],
                'item_id' => $data['item_id'],
                'batch_no' => $data['batch_no'] ?? $this->numbers->batchNo(strtoupper(substr($data['item_type'], 0, 3))),
                'production_date' => $data['production_date'] ?? null,
                'expiry_date' => $data['expiry_date'] ?? null,
                'supplier_id' => $data['supplier_id'] ?? null,
                'source_type' => 'PURCHASE',
                'source_id' => $data['reference_id'] ?? null,
                'initial_qty' => $data['qty'],
                'remaining_qty' => $data['qty'],
                'unit_cost' => $data['unit_cost'],
                'status' => 'AVAILABLE',
            ]);

            $stock = $this->lockStockRow($data['warehouse_id'], $data['item_type'], $data['item_id'], $batch->id);
            $before = (float) $stock->qty;
            $stock->qty = $before + (float) $data['qty'];
            $stock->avg_cost = $this->avgCost($stock->avg_cost, $before, (float) $data['unit_cost'], (float) $data['qty']);
            $stock->save();

            $this->ledger([
                'organization_id' => $batch->organization_id,
                'warehouse_id' => $batch->warehouse_id,
                'batch_id' => $batch->id,
                'item_type' => $data['item_type'],
                'item_id' => $data['item_id'],
                'movement_type' => 'PURCHASE_RECEIPT',
                'direction' => 'IN',
                'qty' => $data['qty'],
                'unit_id' => $data['unit_id'] ?? null,
                'stock_before' => $before,
                'stock_after' => (float) $stock->qty,
                'unit_cost' => $data['unit_cost'],
            ] + $this->refFields($data));

            return $batch->fresh();
        });
    }

    /**
     * Penerimaan non-pembelian (retur baik, hasil koreksi): batch baru + ledger.
     *
     * @param  array{warehouse_id:int,item_type:string,item_id:int,qty:float,unit_id?:int,unit_cost:float,batch_no?:string,expiry_date?:string,reference_type?:string,reference_id?:int,reference_no?:string,movement_date?:string,notes?:string,organization_id?:int}  $data
     */
    public function restock(array $data, string $movementType = 'RETURN'): Batch
    {
        $this->guardQty($data['qty']);

        return DB::transaction(function () use ($data, $movementType) {
            $this->assertNoDuplicate($movementType, $data);

            $batch = Batch::create([
                'organization_id' => $data['organization_id'] ?? $this->orgId(),
                'warehouse_id' => $data['warehouse_id'],
                'item_type' => $data['item_type'],
                'item_id' => $data['item_id'],
                'batch_no' => $data['batch_no'] ?? $this->numbers->batchNo('RTN'),
                'production_date' => $data['production_date'] ?? null,
                'expiry_date' => $data['expiry_date'] ?? null,
                'supplier_id' => null,
                'source_type' => $movementType === 'RETURN' ? 'RETURN' : 'ADJUSTMENT',
                'source_id' => $data['reference_id'] ?? null,
                'initial_qty' => $data['qty'],
                'remaining_qty' => $data['qty'],
                'unit_cost' => $data['unit_cost'],
                'status' => 'AVAILABLE',
            ]);

            $stock = $this->lockStockRow($data['warehouse_id'], $data['item_type'], $data['item_id'], $batch->id);
            $before = (float) $stock->qty;
            $stock->qty = $before + (float) $data['qty'];
            $stock->avg_cost = $this->avgCost($stock->avg_cost, $before, (float) $data['unit_cost'], (float) $data['qty']);
            $stock->save();

            $this->ledger([
                'organization_id' => $batch->organization_id,
                'warehouse_id' => $batch->warehouse_id,
                'batch_id' => $batch->id,
                'item_type' => $data['item_type'],
                'item_id' => $data['item_id'],
                'movement_type' => $movementType,
                'direction' => 'IN',
                'qty' => $data['qty'],
                'unit_id' => $data['unit_id'] ?? null,
                'stock_before' => $before,
                'stock_after' => (float) $stock->qty,
                'unit_cost' => $data['unit_cost'],
            ] + $this->refFields($data));

            return $batch->fresh();
        });
    }

    // ------------------------------------------------------------------
    // OUT: konsumsi FEFO (produksi / waste / delivery / transfer keluar)
    // ------------------------------------------------------------------

    /**
     * Konsumsi $qty memakai alokasi FEFO lintas batch.
     *
     * @return array<int, array{batch_id:int, qty:float}> alokasi per batch
     *
     * @throws InsufficientStockException
     */
    public function consume(int $warehouseId, string $itemType, int $itemId, float $qty, array $meta): array
    {
        $this->guardQty($qty);
        $type = $meta['movement_type'] ?? 'PRODUCTION_CONSUMPTION';
        $this->assertNoDuplicate($type, $meta + ['warehouse_id' => $warehouseId, 'item_type' => $itemType, 'item_id' => $itemId]);

        return DB::transaction(function () use ($warehouseId, $itemType, $itemId, $qty, $meta, $type) {
            $available = $this->availableOf($warehouseId, $itemType, $itemId);
            if ($available < $qty - 1e-9) {
                throw new InsufficientStockException($itemType, $itemId, $warehouseId, $qty, $available);
            }

            $remaining = $qty;
            $allocations = [];

            foreach ($this->fefoBatches($warehouseId, $itemType, $itemId) as $batch) {
                if ($remaining <= 1e-9) {
                    break;
                }
                $take = min((float) $batch->remaining_qty, $remaining);
                if ($take <= 0) {
                    continue;
                }

                $stock = $this->lockStockRow($warehouseId, $itemType, $itemId, $batch->id);
                $take = min($take, (float) $stock->qty);
                if ($take <= 0) {
                    continue;
                }

                $before = (float) $stock->qty;
                $stock->qty = $before - $take;
                $stock->save();

                $batch->remaining_qty = (float) $batch->remaining_qty - $take;
                if ($batch->remaining_qty <= 1e-9) {
                    $batch->remaining_qty = 0;
                    $batch->status = 'DEPLETED';
                }
                $batch->save();

                $this->ledger([
                    'organization_id' => $meta['organization_id'] ?? $batch->organization_id,
                    'warehouse_id' => $warehouseId,
                    'batch_id' => $batch->id,
                    'item_type' => $itemType,
                    'item_id' => $itemId,
                    'movement_type' => $type,
                    'direction' => 'OUT',
                    'qty' => $take,
                    'unit_id' => $meta['unit_id'] ?? null,
                    'stock_before' => $before,
                    'stock_after' => (float) $stock->qty,
                    'unit_cost' => (float) $batch->unit_cost,
                ] + $this->refFields($meta));

                $allocations[] = ['batch_id' => $batch->id, 'qty' => $take, 'unit_cost' => (float) $batch->unit_cost];
                $remaining -= $take;
            }

            if ($remaining > 1e-9) {
                // Seharusnya tidak terjadi karena cek available di awal + lock,
                // tapi guard untuk data race batch kedaluwarsa dsb.
                throw new InsufficientStockException($itemType, $itemId, $warehouseId, $qty, $qty - $remaining);
            }

            return $allocations;
        });
    }

    // ------------------------------------------------------------------
    // IN: hasil produksi — buat batch produk + stok + ledger
    // ------------------------------------------------------------------

    public function produceOutput(array $data): Batch
    {
        $this->guardQty($data['qty']);

        return DB::transaction(function () use ($data) {
            $this->assertNoDuplicate('PRODUCTION_OUTPUT', $data);

            $batch = Batch::create([
                'organization_id' => $data['organization_id'] ?? $this->orgId(),
                'warehouse_id' => $data['warehouse_id'],
                'item_type' => 'product',
                'item_id' => $data['item_id'],
                'batch_no' => $data['batch_no'] ?? $this->numbers->batchNo('PRD'),
                'production_date' => $data['production_date'] ?? now()->toDateString(),
                'expiry_date' => $data['expiry_date'] ?? null,
                'source_type' => 'PRODUCTION',
                'source_id' => $data['reference_id'] ?? null,
                'initial_qty' => $data['qty'],
                'remaining_qty' => $data['qty'],
                'unit_cost' => $data['unit_cost'] ?? 0,
                'status' => 'AVAILABLE',
            ]);

            $stock = $this->lockStockRow($data['warehouse_id'], 'product', $data['item_id'], $batch->id);
            $before = (float) $stock->qty;
            $stock->qty = $before + (float) $data['qty'];
            if ((float) ($data['unit_cost'] ?? 0) > 0) {
                $stock->avg_cost = $this->avgCost($stock->avg_cost, $before, (float) $data['unit_cost'], (float) $data['qty']);
            }
            $stock->save();

            $this->ledger([
                'organization_id' => $batch->organization_id,
                'warehouse_id' => $batch->warehouse_id,
                'batch_id' => $batch->id,
                'item_type' => 'product',
                'item_id' => $data['item_id'],
                'movement_type' => 'PRODUCTION_OUTPUT',
                'direction' => 'IN',
                'qty' => $data['qty'],
                'unit_id' => $data['unit_id'] ?? null,
                'stock_before' => $before,
                'stock_after' => (float) $stock->qty,
                'unit_cost' => $data['unit_cost'] ?? 0,
            ] + $this->refFields($data));

            return $batch->fresh();
        });
    }

    // ------------------------------------------------------------------
    // Adjustment & opname posting
    // ------------------------------------------------------------------

    public function adjust(int $warehouseId, string $itemType, int $itemId, ?int $batchId, float $newQty, array $meta): InventoryMovement
    {
        return DB::transaction(function () use ($warehouseId, $itemType, $itemId, $batchId, $newQty, $meta) {
            $this->assertNoDuplicate('ADJUSTMENT', $meta + ['warehouse_id' => $warehouseId]);

            $stock = $this->lockStockRow($warehouseId, $itemType, $itemId, $batchId);
            $before = (float) $stock->qty;
            $diff = $newQty - $before;

            if (abs($diff) < 1e-9) {
                throw new \InvalidArgumentException('Tidak ada selisih penyesuaian.');
            }

            $stock->qty = $newQty;
            $stock->save();

            if ($batchId) {
                $batch = Batch::lockForUpdate()->findOrFail($batchId);
                $batch->remaining_qty = max(0, (float) $batch->remaining_qty + $diff);
                $batch->status = $batch->remaining_qty <= 0 ? 'DEPLETED' : ($batch->status === 'DEPLETED' ? 'AVAILABLE' : $batch->status);
                $batch->save();
            }

            return $this->ledger([
                'organization_id' => $meta['organization_id'] ?? $this->orgId(),
                'warehouse_id' => $warehouseId,
                'batch_id' => $batchId,
                'item_type' => $itemType,
                'item_id' => $itemId,
                'movement_type' => 'ADJUSTMENT',
                'direction' => $diff > 0 ? 'IN' : 'OUT',
                'qty' => abs($diff),
                'unit_id' => $meta['unit_id'] ?? null,
                'stock_before' => $before,
                'stock_after' => $newQty,
                'unit_cost' => $meta['unit_cost'] ?? 0,
            ] + $this->refFields($meta));
        });
    }

    /** Posting hasil opname: selisih fisik vs sistem menjadi movement STOCK_OPNAME. */
    public function postOpnameItem(int $warehouseId, string $itemType, int $itemId, ?int $batchId, float $systemQty, float $physicalQty, array $meta): ?InventoryMovement
    {
        $diff = $physicalQty - $systemQty;
        if (abs($diff) < 1e-9) {
            return null;
        }

        return DB::transaction(function () use ($warehouseId, $itemType, $itemId, $batchId, $physicalQty, $diff, $meta) {
            $stock = $this->lockStockRow($warehouseId, $itemType, $itemId, $batchId);
            $before = (float) $stock->qty;
            $stock->qty = $physicalQty;
            $stock->save();

            if ($batchId) {
                $batch = Batch::lockForUpdate()->findOrFail($batchId);
                $batch->remaining_qty = max(0, $physicalQty);
                $batch->status = $physicalQty <= 0 ? 'DEPLETED' : 'AVAILABLE';
                $batch->save();
            }

            return $this->ledger([
                'organization_id' => $meta['organization_id'] ?? $this->orgId(),
                'warehouse_id' => $warehouseId,
                'batch_id' => $batchId,
                'item_type' => $itemType,
                'item_id' => $itemId,
                'movement_type' => 'STOCK_OPNAME',
                'direction' => $diff > 0 ? 'IN' : 'OUT',
                'qty' => abs($diff),
                'unit_id' => $meta['unit_id'] ?? null,
                'stock_before' => $before,
                'stock_after' => $physicalQty,
                'unit_cost' => $meta['unit_cost'] ?? 0,
            ] + $this->refFields($meta));
        });
    }

    // ------------------------------------------------------------------
    // Transfer antar gudang (OUT sumber FEFO + IN tujuan batch baru)
    // ------------------------------------------------------------------

    public function transfer(int $fromWarehouseId, int $toWarehouseId, string $itemType, int $itemId, float $qty, array $meta): array
    {
        if ($fromWarehouseId === $toWarehouseId) {
            throw new \InvalidArgumentException('Gudang asal dan tujuan tidak boleh sama.');
        }

        return DB::transaction(function () use ($fromWarehouseId, $toWarehouseId, $itemType, $itemId, $qty, $meta) {
            $allocs = $this->consume($fromWarehouseId, $itemType, $itemId, $qty, $meta + ['movement_type' => 'TRANSFER']);

            $inBatches = [];
            foreach ($allocs as $a) {
                $src = Batch::find($a['batch_id']);
                $inBatches[] = $this->receive([
                    'organization_id' => $meta['organization_id'] ?? $this->orgId(),
                    'warehouse_id' => $toWarehouseId,
                    'item_type' => $itemType,
                    'item_id' => $itemId,
                    'qty' => $a['qty'],
                    'unit_id' => $meta['unit_id'] ?? null,
                    'unit_cost' => $a['unit_cost'],
                    'expiry_date' => $src?->expiry_date?->toDateString(),
                    'production_date' => $src?->production_date?->toDateString(),
                    'movement_date' => $meta['movement_date'] ?? null,
                    'notes' => 'Transfer dari gudang #'.$fromWarehouseId,
                ] + $this->refFields($meta));
            }

            return $inBatches;
        });
    }

    // ------------------------------------------------------------------
    // Reservasi stok (untuk alokasi delivery/produksi tanpa mutasi fisik)
    // ------------------------------------------------------------------

    public function reserve(int $warehouseId, string $itemType, int $itemId, float $qty): void
    {
        DB::transaction(function () use ($warehouseId, $itemType, $itemId, $qty) {
            $totalAvailable = $this->availableOf($warehouseId, $itemType, $itemId);
            if ($totalAvailable < $qty - 1e-9) {
                throw new InsufficientStockException($itemType, $itemId, $warehouseId, $qty, $totalAvailable);
            }
            $remaining = $qty;
            $rows = InventoryStock::where('warehouse_id', $warehouseId)
                ->where('item_type', $itemType)
                ->where('item_id', $itemId)
                ->whereRaw('qty > reserved_qty')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();
            foreach ($rows as $row) {
                if ($remaining <= 1e-9) {
                    break;
                }
                $free = (float) $row->qty - (float) $row->reserved_qty;
                $take = min($free, $remaining);
                $row->reserved_qty = (float) $row->reserved_qty + $take;
                $row->save();
                $remaining -= $take;
            }
        });
    }

    public function releaseReservation(int $warehouseId, string $itemType, int $itemId, float $qty): void
    {
        DB::transaction(function () use ($warehouseId, $itemType, $itemId, $qty) {
            $remaining = $qty;
            $rows = InventoryStock::where('warehouse_id', $warehouseId)
                ->where('item_type', $itemType)
                ->where('item_id', $itemId)
                ->where('reserved_qty', '>', 0)
                ->orderBy('id')
                ->lockForUpdate()
                ->get();
            foreach ($rows as $row) {
                if ($remaining <= 1e-9) {
                    break;
                }
                $take = min((float) $row->reserved_qty, $remaining);
                $row->reserved_qty = (float) $row->reserved_qty - $take;
                $row->save();
                $remaining -= $take;
            }
        });
    }

    // ------------------------------------------------------------------
    // Internals
    // ------------------------------------------------------------------

    protected function lockStockRow(int $warehouseId, string $itemType, int $itemId, ?int $batchId): InventoryStock
    {
        $row = InventoryStock::where('warehouse_id', $warehouseId)
            ->where('item_type', $itemType)
            ->where('item_id', $itemId)
            ->where('batch_id', $batchId)
            ->lockForUpdate()
            ->first();

        if (! $row) {
            $row = new InventoryStock([
                'warehouse_id' => $warehouseId,
                'item_type' => $itemType,
                'item_id' => $itemId,
                'batch_id' => $batchId,
                'qty' => 0,
                'reserved_qty' => 0,
                'avg_cost' => 0,
            ]);
            $row->save();
            $row = InventoryStock::where('warehouse_id', $warehouseId)
                ->where('item_type', $itemType)
                ->where('item_id', $itemId)
                ->where('batch_id', $batchId)
                ->lockForUpdate()
                ->first();
        }

        return $row;
    }

    protected function ledger(array $data): InventoryMovement
    {
        return InventoryMovement::create([
            'organization_id' => $data['organization_id'] ?? $this->orgId(),
            'warehouse_id' => $data['warehouse_id'],
            'batch_id' => $data['batch_id'] ?? null,
            'item_type' => $data['item_type'],
            'item_id' => $data['item_id'],
            'movement_type' => $data['movement_type'],
            'direction' => $data['direction'],
            'qty' => $data['qty'],
            'unit_id' => $data['unit_id'] ?? null,
            'qty_base' => $data['qty'],
            'stock_before' => $data['stock_before'] ?? 0,
            'stock_after' => $data['stock_after'] ?? 0,
            'unit_cost' => $data['unit_cost'] ?? 0,
            'total_cost' => ((float) ($data['qty'] ?? 0)) * ((float) ($data['unit_cost'] ?? 0)),
            'reference_type' => $data['reference_type'] ?? null,
            'reference_id' => $data['reference_id'] ?? null,
            'reference_no' => $data['reference_no'] ?? null,
            'movement_date' => $data['movement_date'] ?? now()->toDateString(),
            'notes' => $data['notes'] ?? null,
            'created_by' => Auth::id(),
        ]);
    }

    /** Cegah posting ganda untuk referensi yang sama (idempotency). */
    protected function assertNoDuplicate(string $type, array $data): void
    {
        $refType = $data['reference_type'] ?? null;
        $refId = $data['reference_id'] ?? null;
        if (! $refType || ! $refId) {
            return;
        }
        $exists = InventoryMovement::where('movement_type', $type)
            ->where('reference_type', $refType)
            ->where('reference_id', $refId)
            ->when(isset($data['warehouse_id']), fn ($q) => $q->where('warehouse_id', $data['warehouse_id']))
            ->exists();
        if ($exists) {
            throw new \RuntimeException("Transaksi {$type} untuk referensi {$refType}#{$refId} sudah pernah diposting (duplikat ditolak).");
        }
    }

    protected function refFields(array $data): array
    {
        return [
            'reference_type' => $data['reference_type'] ?? null,
            'reference_id' => $data['reference_id'] ?? null,
            'reference_no' => $data['reference_no'] ?? null,
            'movement_date' => $data['movement_date'] ?? now()->toDateString(),
            'notes' => $data['notes'] ?? null,
        ];
    }

    protected function avgCost(float $currentAvg, float $currentQty, float $inCost, float $inQty): float
    {
        $total = $currentQty + $inQty;
        if ($total <= 0) {
            return $inCost;
        }

        return round((($currentAvg * $currentQty) + ($inCost * $inQty)) / $total, 2);
    }

    protected function guardQty(float $qty): void
    {
        if ($qty <= 0) {
            throw new \InvalidArgumentException('Qty harus lebih besar dari nol.');
        }
    }

    protected function orgId(): ?int
    {
        try {
            return Auth::user()?->organization_id;
        } catch (\Throwable) {
            return null;
        }
    }
}
