<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Approval extends Model
{
    protected $fillable = ['organization_id', 'approvable_type', 'approvable_id', 'action', 'level', 'status', 'comment', 'requested_by', 'decided_by', 'decided_at'];

    protected function casts(): array
    {
        return ['decided_at' => 'datetime'];
    }

    public function approvable()
    {
        return $this->morphTo(__FUNCTION__, 'approvable_type', 'approvable_id');
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function decider()
    {
        return $this->belongsTo(User::class, 'decided_by');
    }
}
