<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarehouseZone extends Model
{
    protected $fillable = ['warehouse_id', 'code', 'name', 'zone_type'];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function racks()
    {
        return $this->hasMany(WarehouseRack::class);
    }
}
