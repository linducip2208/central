<?php

namespace App\Models;

use App\Core\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class Rfq extends Model
{
    use Auditable;

    protected $fillable = ['organization_id', 'central_kitchen_id', 'purchase_request_id', 'number', 'rfq_date', 'deadline', 'status', 'notes', 'created_by'];

    protected function casts(): array
    {
        return ['rfq_date' => 'date', 'deadline' => 'date'];
    }

    public function items()
    {
        return $this->hasMany(RfqItem::class);
    }

    public function suppliers()
    {
        return $this->belongsToMany(Supplier::class, 'rfq_suppliers')->withTimestamps();
    }

    public function quotations()
    {
        return $this->hasMany(Quotation::class);
    }
}
