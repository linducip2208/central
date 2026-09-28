<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IngredientSubstitution extends Model
{
    protected $fillable = ['ingredient_id', 'substitute_id', 'ratio', 'notes', 'is_approved', 'approved_by'];

    protected function casts(): array
    {
        return ['ratio' => 'decimal:4', 'is_approved' => 'boolean'];
    }

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function substitute()
    {
        return $this->belongsTo(Ingredient::class, 'substitute_id');
    }
}
