<?php

namespace App\Models;

use App\Core\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class Recipe extends Model
{
    use Auditable;

    protected $fillable = ['organization_id', 'product_id', 'code', 'name', 'version', 'yield_qty', 'yield_unit_id', 'instructions', 'cook_time_minutes', 'is_active'];

    protected function casts(): array
    {
        return ['yield_qty' => 'decimal:3', 'is_active' => 'boolean'];
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function yieldUnit()
    {
        return $this->belongsTo(Unit::class, 'yield_unit_id');
    }

    public function items()
    {
        return $this->hasMany(RecipeItem::class);
    }

    public function nutrition()
    {
        return $this->hasMany(NutritionRecord::class);
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }
}
