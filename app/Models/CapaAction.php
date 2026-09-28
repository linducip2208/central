<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CapaAction extends Model
{
    protected $fillable = ['non_conformance_id', 'action_type', 'action', 'owner_id', 'due_date', 'completed_date', 'status'];

    protected function casts(): array
    {
        return ['due_date' => 'date', 'completed_date' => 'date'];
    }

    public function nonConformance()
    {
        return $this->belongsTo(NonConformance::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}
