<?php

namespace App\Models;

use App\Core\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use Auditable, SoftDeletes;

    public const ITEM_TYPE = 'product';

    protected $fillable = ['organization_id', 'code', 'name', 'slug', 'category', 'unit_id', 'standard_cost', 'portion_size_gram', 'description', 'is_active', 'barcode'];

    protected function casts(): array
    {
        return ['standard_cost' => 'decimal:2', 'is_active' => 'boolean'];
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function recipes()
    {
        return $this->hasMany(Recipe::class);
    }

    public function activeRecipe()
    {
        return $this->hasOne(Recipe::class)->where('is_active', true)->latestOfMany();
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
