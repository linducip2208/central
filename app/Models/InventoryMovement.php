<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryMovement extends Model
{
    public const TYPES = [
        'PURCHASE_RECEIPT', 'STOCK_IN', 'STOCK_OUT',
        'PRODUCTION_CONSUMPTION', 'PRODUCTION_OUTPUT',
        'TRANSFER', 'ADJUSTMENT', 'STOCK_OPNAME',
        'WASTE', 'DELIVERY', 'RETURN',
    ];

    protected $fillable = ['organization_id', 'warehouse_id', 'batch_id', 'item_type', 'item_id', 'movement_type', 'direction', 'qty', 'unit_id', 'qty_base', 'stock_before', 'stock_after', 'unit_cost', 'total_cost', 'reference_type', 'reference_id', 'reference_no', 'movement_date', 'notes', 'created_by'];

    protected function casts(): array
    {
        return [
            'qty' => 'decimal:3', 'qty_base' => 'decimal:3',
            'stock_before' => 'decimal:3', 'stock_after' => 'decimal:3',
            'unit_cost' => 'decimal:2', 'total_cost' => 'decimal:2',
            'movement_date' => 'date',
        ];
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeOfItem($q, string $type, int $id)
    {
        return $q->where('item_type', $type)->where('item_id', $id);
    }

    public function scopeInWarehouse($q, int $warehouseId)
    {
        return $q->where('warehouse_id', $warehouseId);
    }
}
