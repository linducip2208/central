<?php

namespace App\Models;

use App\Core\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Delivery extends Model
{
    use Auditable, SoftDeletes;

    protected $fillable = ['organization_id', 'central_kitchen_id', 'distribution_id', 'delivery_route_id', 'vehicle_id', 'stop_sequence', 'school_id', 'number', 'delivery_date', 'qty_planned', 'qty_delivered', 'qty_returned', 'status', 'dispatched_at', 'eta', 'actual_arrival', 'delivered_at', 'received_by_name', 'delivery_proof', 'temperature_c', 'notes', 'courier_id'];

    protected function casts(): array
    {
        return [
            'delivery_date' => 'date',
            'dispatched_at' => 'datetime', 'delivered_at' => 'datetime',
            'eta' => 'datetime', 'actual_arrival' => 'datetime',
            'temperature_c' => 'decimal:2',
        ];
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function distribution()
    {
        return $this->belongsTo(Distribution::class);
    }

    public function items()
    {
        return $this->hasMany(DeliveryItem::class);
    }

    public function trackings()
    {
        return $this->hasMany(DeliveryTracking::class);
    }

    public function courier()
    {
        return $this->belongsTo(User::class, 'courier_id');
    }

    public function route()
    {
        return $this->belongsTo(DeliveryRoute::class, 'delivery_route_id');
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function confirmation()
    {
        return $this->hasOne(SchoolConfirmation::class);
    }

    public function isLate(): bool
    {
        return $this->eta && $this->actual_arrival && $this->actual_arrival->gt($this->eta);
    }
}
