<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PeriodClosing extends Model
{
    protected $fillable = ['organization_id', 'central_kitchen_id', 'closed_before', 'created_by'];

    protected function casts(): array
    {
        return ['closed_before' => 'date'];
    }
}
