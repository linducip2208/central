<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InspectionTemplate extends Model
{
    protected $fillable = ['organization_id', 'code', 'name', 'stage', 'parameters', 'is_active'];

    protected function casts(): array
    {
        return ['parameters' => 'array', 'is_active' => 'boolean'];
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }
}
