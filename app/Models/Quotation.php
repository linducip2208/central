<?php

namespace App\Models;

use App\Core\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class Quotation extends Model
{
    use Auditable;

    protected $fillable = ['rfq_id', 'supplier_id', 'number', 'quotation_date', 'lead_time_days', 'status', 'notes'];

    protected function casts(): array
    {
        return ['quotation_date' => 'date'];
    }

    public function rfq()
    {
        return $this->belongsTo(Rfq::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items()
    {
        return $this->hasMany(QuotationItem::class);
    }

    public function total(): float
    {
        return (float) $this->items()->selectRaw('SUM(qty_offered * unit_price) as t')->value('t');
    }
}
