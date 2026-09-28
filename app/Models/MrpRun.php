<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MrpRun extends Model
{
    protected $fillable = ['organization_id', 'central_kitchen_id', 'demand_plan_id', 'warehouse_id', 'number', 'run_date', 'status', 'created_by'];

    protected function casts(): array
    {
        return ['run_date' => 'date'];
    }

    public function lines()
    {
        return $this->hasMany(MrpLine::class, 'mrp_run_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
}
