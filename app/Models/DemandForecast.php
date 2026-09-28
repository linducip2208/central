<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DemandForecast extends Model
{
    protected $fillable = ['organization_id', 'central_kitchen_id', 'product_id', 'menu_id', 'period_start', 'period_end', 'version', 'method', 'confidence', 'forecast_qty', 'actual_qty', 'error_pct', 'is_scenario', 'scenario_name', 'scenario_factor', 'status', 'created_by'];

    protected function casts(): array
    {
        return [
            'period_start' => 'date', 'period_end' => 'date',
            'confidence' => 'decimal:2', 'forecast_qty' => 'decimal:2', 'actual_qty' => 'decimal:2',
            'error_pct' => 'decimal:2', 'is_scenario' => 'boolean', 'scenario_factor' => 'decimal:4',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $f) {
            if ($f->actual_qty !== null && (float) $f->actual_qty > 0) {
                $f->error_pct = round(abs((float) $f->forecast_qty - (float) $f->actual_qty) / (float) $f->actual_qty * 100, 2);
            }
        });
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
