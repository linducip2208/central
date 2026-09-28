<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierPriceList extends Model
{
    protected $fillable = ['supplier_id', 'ingredient_id', 'price', 'unit_id', 'moq', 'lead_time_days', 'effective_from', 'effective_to'];

    protected function casts(): array
    {
        return ['price' => 'decimal:2', 'moq' => 'decimal:3', 'effective_from' => 'date', 'effective_to' => 'date'];
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function scopeEffective($q, ?string $date = null)
    {
        $date ??= now()->toDateString();

        return $q->where(fn ($w) => $w->whereNull('effective_from')->orWhere('effective_from', '<=', $date))
            ->where(fn ($w) => $w->whereNull('effective_to')->orWhere('effective_to', '>=', $date));
    }
}
