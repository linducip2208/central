<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierInvoiceItem extends Model
{
    protected $fillable = ['supplier_invoice_id', 'ingredient_id', 'qty', 'unit_price', 'line_total'];

    protected function casts(): array
    {
        return ['qty' => 'decimal:3', 'unit_price' => 'decimal:2', 'line_total' => 'decimal:2'];
    }

    protected static function booted(): void
    {
        static::saving(function (self $item) {
            $item->line_total = (float) $item->qty * (float) $item->unit_price;
        });
    }

    public function invoice()
    {
        return $this->belongsTo(SupplierInvoice::class, 'supplier_invoice_id');
    }

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }
}
