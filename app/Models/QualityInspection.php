<?php

namespace App\Models;

use App\Core\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class QualityInspection extends Model
{
    use Auditable;

    protected $fillable = ['organization_id', 'central_kitchen_id', 'inspection_template_id', 'reference_type', 'reference_id', 'number', 'inspection_date', 'results', 'temperature_c', 'photo_path', 'result', 'notes', 'inspected_by'];

    protected function casts(): array
    {
        return ['inspection_date' => 'date', 'results' => 'array', 'temperature_c' => 'decimal:2'];
    }

    public function template()
    {
        return $this->belongsTo(InspectionTemplate::class, 'inspection_template_id');
    }

    public function inspector()
    {
        return $this->belongsTo(User::class, 'inspected_by');
    }

    public function reference()
    {
        return $this->morphTo(__FUNCTION__, 'reference_type', 'reference_id');
    }

    public function nonConformances()
    {
        return $this->hasMany(NonConformance::class);
    }
}
