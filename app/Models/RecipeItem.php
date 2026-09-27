<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecipeItem extends Model
{
    protected $fillable = ['recipe_id', 'ingredient_id', 'qty', 'unit_id', 'waste_factor_pct', 'notes'];

    protected function casts(): array
    {
        return ['qty' => 'decimal:4', 'waste_factor_pct' => 'decimal:2'];
    }

    public function recipe()
    {
        return $this->belongsTo(Recipe::class);
    }

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    /** Qty needed for $portions, scaled from yield + waste factor. */
    public function requiredFor(float $portions, float $yieldQty): float
    {
        if ($yieldQty <= 0) {
            return 0;
        }
        $base = ((float) $this->qty) * ($portions / $yieldQty);

        return $base * (1 + ((float) $this->waste_factor_pct) / 100);
    }
}
