<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduledReport extends Model
{
    public const DATASETS = ['movements', 'deliveries', 'production', 'waste', 'costing'];

    protected $fillable = ['organization_id', 'dataset', 'frequency', 'is_active', 'last_run_at', 'created_by'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'last_run_at' => 'datetime'];
    }

    public function isDue(): bool
    {
        if (! $this->is_active) {
            return false;
        }
        if (! $this->last_run_at) {
            return true;
        }
        $next = match ($this->frequency) {
            'DAILY' => $this->last_run_at->copy()->addDay(),
            'WEEKLY' => $this->last_run_at->copy()->addWeek(),
            default => $this->last_run_at->copy()->addMonth(),
        };

        return now()->gte($next);
    }
}
