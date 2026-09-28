<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Downtime extends Model
{
    protected $fillable = ['organization_id', 'central_kitchen_id', 'work_center_id', 'production_order_id', 'started_at', 'ended_at', 'reason', 'notes'];

    protected function casts(): array
    {
        return ['started_at' => 'datetime', 'ended_at' => 'datetime'];
    }

    public function workCenter()
    {
        return $this->belongsTo(WorkCenter::class);
    }

    public function durationMinutes(): ?float
    {
        return $this->ended_at ? $this->started_at->diffInMinutes($this->ended_at) : null;
    }
}
