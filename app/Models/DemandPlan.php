<?php

namespace App\Models;

use App\Core\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class DemandPlan extends Model
{
    use Auditable;

    protected $fillable = ['organization_id', 'central_kitchen_id', 'number', 'period_type', 'period_start', 'period_end', 'status', 'notes', 'created_by'];

    protected function casts(): array
    {
        return ['period_start' => 'date', 'period_end' => 'date'];
    }

    public function lines()
    {
        return $this->hasMany(DemandPlanLine::class);
    }

    public function centralKitchen()
    {
        return $this->belongsTo(CentralKitchen::class);
    }
}
