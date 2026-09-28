<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    protected $fillable = ['central_kitchen_id', 'code', 'name', 'start_time', 'end_time', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
