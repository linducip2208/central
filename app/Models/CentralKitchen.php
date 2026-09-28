<?php

namespace App\Models;

use App\Core\Traits\Auditable;
use App\Core\Traits\HasSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CentralKitchen extends Model
{
    use Auditable, HasSlug, SoftDeletes;

    protected $fillable = ['organization_id', 'code', 'name', 'slug', 'address', 'city', 'pic_name', 'pic_phone', 'daily_capacity', 'latitude', 'longitude', 'status'];

    protected function casts(): array
    {
        return ['daily_capacity' => 'integer'];
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function kitchenUnits()
    {
        return $this->hasMany(KitchenUnit::class);
    }

    public function warehouses()
    {
        return $this->hasMany(Warehouse::class);
    }

    public function schools()
    {
        return $this->hasMany(School::class);
    }

    public function scopeActive($q)
    {
        return $q->where('status', 'ACTIVE');
    }
}
