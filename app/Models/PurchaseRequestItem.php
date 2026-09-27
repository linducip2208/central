<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseRequestItem extends Model
{
    protected $fillable = ['purchase_request_id', 'ingredient_id', 'qty_requested', 'qty_approved', 'qty_ordered', 'unit_id', 'estimated_price', 'notes'];

    protected function casts(): array
    {
        return [
            'qty_requested' => 'decimal:3', 'qty_approved' => 'decimal:3',
            'qty_ordered' => 'decimal:3', 'estimated_price' => 'decimal:2',
        ];
    }

    public function purchaseRequest()
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function remainingToOrder(): float
    {
        return max(0, (float) $this->qty_approved - (float) $this->qty_ordered);
    }
}
