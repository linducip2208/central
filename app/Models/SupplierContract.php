<?php

namespace App\Models;

use App\Core\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class SupplierContract extends Model
{
    use Auditable;

    protected $fillable = ['organization_id', 'supplier_id', 'number', 'start_date', 'end_date', 'payment_terms', 'notes', 'status'];

    protected function casts(): array
    {
        return ['start_date' => 'date', 'end_date' => 'date'];
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function isValid(): bool
    {
        return $this->status === 'ACTIVE' && $this->start_date <= now() && $this->end_date >= now();
    }
}
