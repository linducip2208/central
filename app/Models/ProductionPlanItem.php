<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionPlanItem extends Model
{
    protected $fillable = ['production_plan_id', 'product_id', 'planned_qty'];

    public function plan()
    {
        return $this->belongsTo(ProductionPlan::class, 'production_plan_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
