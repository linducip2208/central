<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MealGroup extends Model
{
    protected $fillable = ['organization_id', 'code', 'name', 'dietary_notes', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function recipients()
    {
        return $this->belongsToMany(Recipient::class, 'meal_group_recipient')->withTimestamps();
    }
}
