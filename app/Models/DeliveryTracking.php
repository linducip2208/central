<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryTracking extends Model
{
    protected $fillable = ['delivery_id', 'status', 'latitude', 'longitude', 'notes', 'created_by'];

    protected function casts(): array
    {
        return ['latitude' => 'decimal:7', 'longitude' => 'decimal:7'];
    }

    public function delivery()
    {
        return $this->belongsTo(Delivery::class);
    }
}
