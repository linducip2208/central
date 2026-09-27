<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockOpnameItem extends Model
{
    protected $fillable = ['stock_opname_id', 'item_type', 'item_id', 'batch_id', 'system_qty', 'physical_qty', 'difference', 'unit_cost', 'notes'];

    protected function casts(): array
    {
        return [
            'system_qty' => 'decimal:3', 'physical_qty' => 'decimal:3',
            'difference' => 'decimal:3', 'unit_cost' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $item) {
            $item->difference = (float) $item->physical_qty - (float) $item->system_qty;
        });
    }

    public function opname()
    {
        return $this->belongsTo(StockOpname::class, 'stock_opname_id');
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }
}
