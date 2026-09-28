<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MrpLine extends Model
{
    protected $fillable = ['mrp_run_id', 'ingredient_id', 'gross_requirement', 'on_hand', 'reserved', 'available', 'incoming', 'safety_stock', 'net_requirement', 'suggested_order_qty', 'suggested_supplier_id', 'suggested_price', 'recommendation', 'explanation'];

    protected function casts(): array
    {
        return [
            'gross_requirement' => 'decimal:3', 'on_hand' => 'decimal:3', 'reserved' => 'decimal:3',
            'available' => 'decimal:3', 'incoming' => 'decimal:3', 'safety_stock' => 'decimal:3',
            'net_requirement' => 'decimal:3', 'suggested_order_qty' => 'decimal:3', 'suggested_price' => 'decimal:2',
        ];
    }

    public function run()
    {
        return $this->belongsTo(MrpRun::class, 'mrp_run_id');
    }

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function suggestedSupplier()
    {
        return $this->belongsTo(Supplier::class, 'suggested_supplier_id');
    }
}
