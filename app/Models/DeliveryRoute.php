<?php

namespace App\Models;

use App\Core\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class DeliveryRoute extends Model
{
    use Auditable;

    protected $fillable = ['organization_id', 'central_kitchen_id', 'code', 'name', 'vehicle_id', 'driver_id', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function centralKitchen()
    {
        return $this->belongsTo(CentralKitchen::class);
    }

    public function stops()
    {
        return $this->hasMany(DeliveryRouteStop::class)->orderBy('sequence');
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }
}
