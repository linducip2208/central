<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RfqItem extends Model
{
    protected $fillable = ['rfq_id', 'ingredient_id', 'qty', 'unit_id'];

    protected function casts(): array
    {
        return ['qty' => 'decimal:3'];
    }

    public function rfq()
    {
        return $this->belongsTo(Rfq::class);
    }

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function quotationItems()
    {
        return $this->hasMany(QuotationItem::class);
    }
}
