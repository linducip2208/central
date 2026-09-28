<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    protected $fillable = ['code', 'name', 'max_users', 'max_warehouses', 'api_access', 'is_active'];

    protected function casts(): array
    {
        return ['api_access' => 'boolean', 'is_active' => 'boolean'];
    }
}
