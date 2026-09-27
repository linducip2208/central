<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryStock extends Model
{
    protected $fillable = ['warehouse_id', 'item_type', 'item_id', 'batch_id', 'qty', 'reserved_qty', 'avg_cost'];

    protected function casts(): array
    {
        return ['qty' => 'decimal:3', 'reserved_qty' => 'decimal:3', 'avg_cost' => 'decimal:2'];
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }

    public function availableQty(): float
    {
        return max(0, (float) $this->qty - (float) $this->reserved_qty);
    }
}
