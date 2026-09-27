<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionOrderItem extends Model
{
    protected $fillable = ['production_order_id', 'ingredient_id', 'qty_required', 'qty_consumed', 'unit_id'];

    protected function casts(): array
    {
        return ['qty_required' => 'decimal:4', 'qty_consumed' => 'decimal:4'];
    }

    public function order()
    {
        return $this->belongsTo(ProductionOrder::class, 'production_order_id');
    }

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}
