<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    protected $fillable = ['purchase_order_id', 'purchase_request_item_id', 'ingredient_id', 'qty_ordered', 'qty_received', 'unit_id', 'unit_price', 'line_total'];

    protected function casts(): array
    {
        return [
            'qty_ordered' => 'decimal:3', 'qty_received' => 'decimal:3',
            'unit_price' => 'decimal:2', 'line_total' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $item) {
            $item->line_total = (float) $item->qty_ordered * (float) $item->unit_price;
        });
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function remainingToReceive(): float
    {
        return max(0, (float) $this->qty_ordered - (float) $this->qty_received);
    }
}
