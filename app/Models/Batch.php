<?php

namespace App\Models;

use App\Core\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Batch extends Model
{
    use Auditable, SoftDeletes;

    protected $fillable = ['organization_id', 'warehouse_id', 'bin_id', 'item_type', 'item_id', 'batch_no', 'production_date', 'expiry_date', 'supplier_id', 'source_type', 'source_id', 'initial_qty', 'remaining_qty', 'unit_cost', 'status', 'hold_reason'];

    protected function casts(): array
    {
        return [
            'production_date' => 'date', 'expiry_date' => 'date',
            'initial_qty' => 'decimal:3', 'remaining_qty' => 'decimal:3',
            'unit_cost' => 'decimal:2',
        ];
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function bin()
    {
        return $this->belongsTo(WarehouseBin::class, 'bin_id');
    }

    public function isBlocked(): bool
    {
        return in_array($this->status, ['BLOCKED', 'EXPIRED']);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function stocks()
    {
        return $this->hasMany(InventoryStock::class);
    }

    public function movements()
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function item()
    {
        return $this->item_type === 'product'
            ? $this->belongsTo(Product::class, 'item_id')
            : $this->belongsTo(Ingredient::class, 'item_id');
    }

    public function scopeAvailable($q)
    {
        return $q->where('status', 'AVAILABLE')->where('remaining_qty', '>', 0);
    }

    public function scopeFefo($q)
    {
        return $q->orderByRaw('expiry_date IS NULL, expiry_date ASC')->orderBy('id');
    }

    public function scopeExpiringSoon($q, int $days)
    {
        return $q->where('status', 'AVAILABLE')
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<=', now()->addDays($days));
    }

    public function scopeExpired($q)
    {
        return $q->where('status', '!=', 'DEPLETED')
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<', now()->toDateString());
    }

    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }
}
