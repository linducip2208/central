<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuCycleDay extends Model
{
    protected $fillable = ['menu_cycle_id', 'day_no', 'menu_id'];

    public function cycle()
    {
        return $this->belongsTo(MenuCycle::class, 'menu_cycle_id');
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }
}
