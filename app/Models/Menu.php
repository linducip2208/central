<?php

namespace App\Models;

use App\Core\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Menu extends Model
{
    use Auditable, SoftDeletes;

    protected $fillable = ['organization_id', 'central_kitchen_id', 'code', 'name', 'menu_date', 'meal_type', 'planned_portions', 'budget_per_portion', 'notes', 'status'];

    protected function casts(): array
    {
        return ['menu_date' => 'date', 'budget_per_portion' => 'decimal:2'];
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function centralKitchen()
    {
        return $this->belongsTo(CentralKitchen::class);
    }

    public function items()
    {
        return $this->hasMany(MenuProduct::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'menu_products')->withPivot('qty_per_portion', 'sort_order')->withTimestamps();
    }

    public function nutrition()
    {
        return $this->hasMany(NutritionRecord::class);
    }
}
