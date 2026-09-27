<?php

namespace App\Models;

use App\Core\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class QualityControl extends Model
{
    use Auditable;

    protected $fillable = ['organization_id', 'central_kitchen_id', 'reference_type', 'reference_id', 'number', 'check_date', 'check_type', 'sample_qty', 'pass_qty', 'fail_qty', 'criteria', 'result', 'notes', 'checked_by'];

    protected function casts(): array
    {
        return [
            'check_date' => 'date',
            'sample_qty' => 'decimal:3', 'pass_qty' => 'decimal:3', 'fail_qty' => 'decimal:3',
            'criteria' => 'array',
        ];
    }

    public function checker()
    {
        return $this->belongsTo(User::class, 'checked_by');
    }

    public function reference()
    {
        return $this->morphTo(__FUNCTION__, 'reference_type', 'reference_id');
    }
}
