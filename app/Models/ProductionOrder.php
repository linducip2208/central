<?php

namespace App\Models;

use App\Core\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductionOrder extends Model
{
    use Auditable, SoftDeletes;

    protected $fillable = ['organization_id', 'central_kitchen_id', 'kitchen_unit_id', 'work_center_id', 'shift_id', 'production_plan_id', 'menu_id', 'product_id', 'recipe_id', 'number', 'production_date', 'planned_qty', 'produced_qty', 'rejected_qty', 'rework_qty', 'unit_id', 'status', 'material_status', 'started_at', 'completed_at', 'notes', 'theoretical_cost', 'created_by'];

    protected function casts(): array
    {
        return [
            'production_date' => 'date',
            'planned_qty' => 'decimal:3', 'produced_qty' => 'decimal:3', 'rejected_qty' => 'decimal:3',
            'started_at' => 'datetime', 'completed_at' => 'datetime',
        ];
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function recipe()
    {
        return $this->belongsTo(Recipe::class);
    }

    public function centralKitchen()
    {
        return $this->belongsTo(CentralKitchen::class);
    }

    public function kitchenUnit()
    {
        return $this->belongsTo(KitchenUnit::class);
    }

    public function workCenter()
    {
        return $this->belongsTo(WorkCenter::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function operators()
    {
        return $this->hasMany(ProductionOrderOperator::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function items()
    {
        return $this->hasMany(ProductionOrderItem::class);
    }

    public function qualityControls()
    {
        return $this->hasMany(QualityControl::class, 'reference_id')->where('reference_type', self::class);
    }

    public function isCompletable(): bool
    {
        return in_array($this->status, ['RELEASED', 'IN_PROGRESS', 'PARTIAL']);
    }

    public function completionPct(): float
    {
        if ((float) $this->planned_qty <= 0) {
            return 0;
        }

        return round(((float) $this->produced_qty / (float) $this->planned_qty) * 100, 1);
    }
}
