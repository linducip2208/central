<?php

namespace App\Models;

use App\Core\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Packaging extends Model
{
    use Auditable, SoftDeletes;

    protected $table = 'packagings';

    protected $fillable = ['organization_id', 'central_kitchen_id', 'production_order_id', 'warehouse_id', 'number', 'packaging_date', 'packages_planned', 'packages_done', 'package_type', 'status', 'notes', 'created_by'];

    protected function casts(): array
    {
        return ['packaging_date' => 'date'];
    }

    public function productionOrder()
    {
        return $this->belongsTo(ProductionOrder::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items()
    {
        return $this->hasMany(PackagingItem::class);
    }

    public function materialUsages()
    {
        return $this->hasMany(PackagingMaterialUsage::class);
    }
}
