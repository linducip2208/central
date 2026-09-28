<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryRouteStop extends Model
{
    protected $fillable = ['delivery_route_id', 'school_id', 'sequence', 'window_start', 'window_end'];

    public function route()
    {
        return $this->belongsTo(DeliveryRoute::class, 'delivery_route_id');
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }
}
