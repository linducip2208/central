<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaintenanceLog extends Model
{
    protected $fillable = ['equipment_id', 'maintenance_type', 'description', 'performed_at', 'cost', 'performed_by'];

    protected function casts(): array
    {
        return ['performed_at' => 'date', 'cost' => 'decimal:2'];
    }

    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }
}
