<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkCenter extends Model
{
    protected $fillable = ['central_kitchen_id', 'code', 'name', 'center_type', 'capacity_per_hour', 'operators_required', 'status'];

    public function centralKitchen()
    {
        return $this->belongsTo(CentralKitchen::class);
    }

    public function scopeActive($q)
    {
        return $q->where('status', 'ACTIVE');
    }
}
