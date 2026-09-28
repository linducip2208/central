<?php

namespace App\Models;

use App\Core\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class MenuCycle extends Model
{
    use Auditable;

    protected $fillable = ['organization_id', 'code', 'name', 'cycle_days', 'start_date', 'status'];

    protected function casts(): array
    {
        return ['start_date' => 'date'];
    }

    public function days()
    {
        return $this->hasMany(MenuCycleDay::class)->orderBy('day_no');
    }
}
