<?php

namespace App\Models;

use App\Core\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class NonConformance extends Model
{
    use Auditable;

    protected $fillable = ['organization_id', 'central_kitchen_id', 'quality_inspection_id', 'batch_id', 'number', 'category', 'severity', 'description', 'disposition', 'status', 'reported_by'];

    public function inspection()
    {
        return $this->belongsTo(QualityInspection::class, 'quality_inspection_id');
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }

    public function capaActions()
    {
        return $this->hasMany(CapaAction::class);
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }
}
