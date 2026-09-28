<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AutomationRule extends Model
{
    public const EVENTS = ['stock.low', 'stock.expiry', 'qc.failed', 'delivery.delayed', 'capa.overdue', 'invoice.variance', 'supplier.degraded'];

    protected $fillable = ['organization_id', 'name', 'event', 'conditions', 'action', 'target_role', 'message', 'is_active', 'last_fired_at'];

    protected function casts(): array
    {
        return ['conditions' => 'array', 'is_active' => 'boolean', 'last_fired_at' => 'datetime'];
    }
}
