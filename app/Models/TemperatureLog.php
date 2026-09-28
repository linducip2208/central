<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TemperatureLog extends Model
{
    public const SPECS = [
        'COLD_STORAGE' => ['min' => 0, 'max' => 4],
        'FROZEN' => ['min' => -25, 'max' => -18],
        'COOKING' => ['min' => 75, 'max' => null],
        'COOLING' => ['min' => null, 'max' => 5],
        'SERVING' => ['min' => 60, 'max' => null],
        'TRANSPORT' => ['min' => null, 'max' => 10],
    ];

    protected $fillable = ['organization_id', 'central_kitchen_id', 'checkpoint', 'temperature_c', 'spec_min', 'spec_max', 'in_spec', 'logged_at', 'corrective_action', 'logged_by'];

    protected function casts(): array
    {
        return ['temperature_c' => 'decimal:2', 'spec_min' => 'decimal:2', 'spec_max' => 'decimal:2', 'in_spec' => 'boolean', 'logged_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::saving(function (self $log) {
            if ($log->spec_min === null && $log->spec_max === null && isset(self::SPECS[$log->checkpoint])) {
                [$log->spec_min, $log->spec_max] = [self::SPECS[$log->checkpoint]['min'], self::SPECS[$log->checkpoint]['max']];
            }
            $ok = true;
            if ($log->spec_min !== null && (float) $log->temperature_c < (float) $log->spec_min) {
                $ok = false;
            }
            if ($log->spec_max !== null && (float) $log->temperature_c > (float) $log->spec_max) {
                $ok = false;
            }
            $log->in_spec = $ok;
        });
    }

    public function logger()
    {
        return $this->belongsTo(User::class, 'logged_by');
    }
}
