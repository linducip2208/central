<?php

namespace App\Models;

use App\Core\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class Recall extends Model
{
    use Auditable;

    protected $fillable = ['organization_id', 'central_kitchen_id', 'trigger_batch_id', 'number', 'reason', 'severity', 'description', 'status', 'actions_taken', 'created_by', 'approved_by', 'approved_at'];

    protected function casts(): array
    {
        return ['approved_at' => 'datetime'];
    }

    public function triggerBatch()
    {
        return $this->belongsTo(Batch::class, 'trigger_batch_id');
    }

    public function items()
    {
        return $this->hasMany(RecallItem::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'ACTIVE';
    }
}
