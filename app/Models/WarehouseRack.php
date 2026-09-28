<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarehouseRack extends Model
{
    protected $fillable = ['warehouse_zone_id', 'code', 'name'];

    public function zone()
    {
        return $this->belongsTo(WarehouseZone::class, 'warehouse_zone_id');
    }

    public function bins()
    {
        return $this->hasMany(WarehouseBin::class);
    }
}
