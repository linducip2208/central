<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PackagingMaterialUsage extends Model
{
    protected $fillable = ['packaging_id', 'ingredient_id', 'batch_id', 'qty_used'];

    protected function casts(): array
    {
        return ['qty_used' => 'decimal:3'];
    }

    public function packaging()
    {
        return $this->belongsTo(Packaging::class);
    }

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }
}
