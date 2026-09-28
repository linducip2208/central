<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuotationItem extends Model
{
    protected $fillable = ['quotation_id', 'rfq_item_id', 'unit_price', 'qty_offered'];

    protected function casts(): array
    {
        return ['unit_price' => 'decimal:2', 'qty_offered' => 'decimal:3'];
    }

    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }

    public function rfqItem()
    {
        return $this->belongsTo(RfqItem::class);
    }
}
