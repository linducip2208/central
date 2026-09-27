<?php

namespace App\Models;

use App\Core\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Distribution extends Model
{
    use Auditable, SoftDeletes;

    protected $fillable = ['organization_id', 'central_kitchen_id', 'packaging_id', 'number', 'distribution_date', 'total_portions', 'vehicle_no', 'driver_name', 'status', 'notes', 'created_by'];

    protected function casts(): array
    {
        return ['distribution_date' => 'date'];
    }

    public function centralKitchen()
    {
        return $this->belongsTo(CentralKitchen::class);
    }

    public function packaging()
    {
        return $this->belongsTo(Packaging::class);
    }

    public function items()
    {
        return $this->hasMany(DistributionItem::class);
    }

    public function deliveries()
    {
        return $this->hasMany(Delivery::class);
    }
}
