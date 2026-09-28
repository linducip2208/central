<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Allergen extends Model
{
    protected $fillable = ['code', 'name', 'description'];

    public function ingredients()
    {
        return $this->belongsToMany(Ingredient::class, 'allergen_ingredient')->withTimestamps();
    }

    public function recipients()
    {
        return $this->belongsToMany(Recipient::class, 'allergen_recipient')->withPivot('severity')->withTimestamps();
    }
}
