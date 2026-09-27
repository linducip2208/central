<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Costing extends Model
{
    protected $fillable = ['organization_id', 'central_kitchen_id', 'production_order_id', 'menu_id', 'costing_date', 'material_cost', 'labor_cost', 'overhead_cost', 'packaging_cost', 'delivery_cost', 'total_cost', 'portions', 'cost_per_portion', 'method', 'notes'];

    protected function casts(): array
    {
        return [
            'costing_date' => 'date',
            'material_cost' => 'decimal:2', 'labor_cost' => 'decimal:2',
            'overhead_cost' => 'decimal:2', 'packaging_cost' => 'decimal:2',
            'delivery_cost' => 'decimal:2', 'total_cost' => 'decimal:2',
            'cost_per_portion' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $c) {
            $c->total_cost = (float) $c->material_cost + (float) $c->labor_cost + (float) $c->overhead_cost + (float) $c->packaging_cost + (float) $c->delivery_cost;
            $c->cost_per_portion = $c->portions > 0 ? round((float) $c->total_cost / (int) $c->portions, 2) : 0;
        });
    }

    public function productionOrder()
    {
        return $this->belongsTo(ProductionOrder::class);
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }
}
