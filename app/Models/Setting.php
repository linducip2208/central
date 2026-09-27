<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['organization_id', 'group', 'key', 'value'];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}
