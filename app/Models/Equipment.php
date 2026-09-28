<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Equipment extends Model
{
    protected $table = 'equipment';

    protected $fillable = ['central_kitchen_id', 'work_center_id', 'code', 'name', 'last_maintenance_at', 'next_maintenance_at', 'status'];

    protected function casts(): array
    {
        return ['last_maintenance_at' => 'date', 'next_maintenance_at' => 'date'];
    }

    public function centralKitchen()
    {
        return $this->belongsTo(CentralKitchen::class);
    }

    public function workCenter()
    {
        return $this->belongsTo(WorkCenter::class);
    }

    public function logs()
    {
        return $this->hasMany(MaintenanceLog::class);
    }

    public function isOverdue(): bool
    {
        return $this->next_maintenance_at && $this->next_maintenance_at->isPast();
    }
}
