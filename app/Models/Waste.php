<?php

namespace App\Models;

use App\Core\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Waste extends Model
{
    use Auditable, SoftDeletes;

    protected $fillable = ['organization_id', 'central_kitchen_id', 'warehouse_id', 'number', 'waste_date', 'item_type', 'item_id', 'batch_id', 'qty', 'unit_id', 'cost_loss', 'reason', 'disposal_method', 'notes', 'reported_by'];

    protected function casts(): array
    {
        return ['waste_date' => 'date', 'qty' => 'decimal:3', 'cost_loss' => 'decimal:2'];
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}
