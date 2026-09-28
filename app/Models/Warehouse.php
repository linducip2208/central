<?php

namespace App\Models;

use App\Core\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Warehouse extends Model
{
    use Auditable, SoftDeletes;

    protected $fillable = ['central_kitchen_id', 'code', 'name', 'warehouse_type', 'location', 'pic_name', 'is_default', 'status'];

    protected function casts(): array
    {
        return ['is_default' => 'boolean'];
    }

    public function centralKitchen()
    {
        return $this->belongsTo(CentralKitchen::class);
    }

    public function batches()
    {
        return $this->hasMany(Batch::class);
    }

    public function stocks()
    {
        return $this->hasMany(InventoryStock::class);
    }

    public function movements()
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function zones()
    {
        return $this->hasMany(WarehouseZone::class);
    }
}
