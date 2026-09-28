<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierContact extends Model
{
    protected $fillable = ['supplier_id', 'name', 'position', 'phone', 'email', 'is_primary'];

    protected function casts(): array
    {
        return ['is_primary' => 'boolean'];
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
