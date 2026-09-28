<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DemandPlanLine extends Model
{
    protected $fillable = ['demand_plan_id', 'school_id', 'menu_id', 'product_id', 'demand_date', 'gross_demand', 'attendance_adjustment', 'manual_adjustment', 'adjusted_demand', 'safety_stock', 'net_demand', 'source'];

    protected function casts(): array
    {
        return ['demand_date' => 'date'];
    }

    protected static function booted(): void
    {
        static::saving(function (self $l) {
            $l->adjusted_demand = max(0, (int) $l->gross_demand - (int) $l->attendance_adjustment + (int) $l->manual_adjustment);
            $l->net_demand = (int) $l->adjusted_demand + (int) $l->safety_stock;
        });
    }

    public function plan()
    {
        return $this->belongsTo(DemandPlan::class, 'demand_plan_id');
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
