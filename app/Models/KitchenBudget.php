<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KitchenBudget extends Model
{
    protected $fillable = ['organization_id', 'central_kitchen_id', 'period', 'amount', 'notes'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2'];
    }

    public function centralKitchen()
    {
        return $this->belongsTo(CentralKitchen::class);
    }
}
