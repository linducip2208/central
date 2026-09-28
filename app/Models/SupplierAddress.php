<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierAddress extends Model
{
    protected $fillable = ['supplier_id', 'label', 'address', 'city', 'is_default'];

    protected function casts(): array
    {
        return ['is_default' => 'boolean'];
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
