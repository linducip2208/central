<?php

namespace App\Models;

use App\Core\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class School extends Model
{
    use Auditable, SoftDeletes;

    protected $fillable = ['organization_id', 'central_kitchen_id', 'npsn', 'code', 'name', 'level', 'address', 'district', 'city', 'pic_name', 'pic_phone', 'student_count', 'target_portions', 'distance_km', 'latitude', 'longitude', 'geofence_radius_m', 'status'];

    protected function casts(): array
    {
        return ['distance_km' => 'decimal:2'];
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function centralKitchen()
    {
        return $this->belongsTo(CentralKitchen::class);
    }

    public function recipients()
    {
        return $this->hasMany(Recipient::class);
    }

    public function deliveries()
    {
        return $this->hasMany(Delivery::class);
    }

    public function scopeActive($q)
    {
        return $q->where('status', 'ACTIVE');
    }
}
