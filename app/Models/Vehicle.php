<?php

namespace App\Models;

use App\Core\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use Auditable, SoftDeletes;

    protected $fillable = ['organization_id', 'central_kitchen_id', 'plate_no', 'name', 'vehicle_type', 'capacity_portions', 'has_cooler', 'status'];

    protected function casts(): array
    {
        return ['has_cooler' => 'boolean'];
    }

    public function centralKitchen()
    {
        return $this->belongsTo(CentralKitchen::class);
    }

    public function scopeActive($q)
    {
        return $q->where('status', 'ACTIVE');
    }
}
