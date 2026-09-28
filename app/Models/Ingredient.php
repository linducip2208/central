<?php

namespace App\Models;

use App\Core\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ingredient extends Model
{
    use Auditable, SoftDeletes;

    public const ITEM_TYPE = 'ingredient';

    protected $fillable = ['organization_id', 'code', 'name', 'slug', 'category', 'unit_id', 'standard_price', 'min_stock', 'reorder_point', 'safety_stock', 'lead_time_days', 'moq', 'order_multiple', 'preferred_supplier_id', 'max_stock', 'shelf_life_days', 'requires_batch', 'is_active', 'barcode'];

    protected function casts(): array
    {
        return [
            'standard_price' => 'decimal:2',
            'min_stock' => 'decimal:3',
            'max_stock' => 'decimal:3',
            'requires_batch' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function recipeItems()
    {
        return $this->hasMany(RecipeItem::class);
    }

    public function allergens()
    {
        return $this->belongsToMany(Allergen::class, 'allergen_ingredient')->withTimestamps();
    }

    public function preferredSupplier()
    {
        return $this->belongsTo(Supplier::class, 'preferred_supplier_id');
    }

    public function priceLists()
    {
        return $this->hasMany(SupplierPriceList::class);
    }

    public function substitutions()
    {
        return $this->hasMany(IngredientSubstitution::class);
    }

    public function approvedSubstitutes()
    {
        return $this->hasMany(IngredientSubstitution::class)->where('is_approved', true)->with('substitute');
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }

    public function itemType(): string
    {
        return self::ITEM_TYPE;
    }
}
