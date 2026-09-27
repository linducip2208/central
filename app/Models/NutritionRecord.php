<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NutritionRecord extends Model
{
    protected $fillable = ['product_id', 'menu_id', 'recipe_id', 'calories', 'protein_g', 'carbs_g', 'fat_g', 'fiber_g', 'sugar_g', 'sodium_mg', 'serving_size_g', 'source'];

    protected function casts(): array
    {
        return [
            'calories' => 'decimal:2', 'protein_g' => 'decimal:2', 'carbs_g' => 'decimal:2',
            'fat_g' => 'decimal:2', 'fiber_g' => 'decimal:2', 'sugar_g' => 'decimal:2',
            'sodium_mg' => 'decimal:2', 'serving_size_g' => 'decimal:2',
        ];
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }

    public function recipe()
    {
        return $this->belongsTo(Recipe::class);
    }
}
