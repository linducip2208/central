<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionOrderOperator extends Model
{
    protected $fillable = ['production_order_id', 'user_id', 'work_center_id', 'role', 'assigned_at'];

    protected function casts(): array
    {
        return ['assigned_at' => 'datetime'];
    }

    public function order()
    {
        return $this->belongsTo(ProductionOrder::class, 'production_order_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function workCenter()
    {
        return $this->belongsTo(WorkCenter::class);
    }
}
