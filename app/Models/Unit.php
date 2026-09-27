<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $fillable = ['code', 'name', 'symbol', 'unit_type', 'is_base', 'is_active'];

    protected function casts(): array
    {
        return ['is_base' => 'boolean', 'is_active' => 'boolean'];
    }

    public function conversionsFrom()
    {
        return $this->hasMany(UnitConversion::class, 'from_unit_id');
    }

    public function conversionsTo()
    {
        return $this->hasMany(UnitConversion::class, 'to_unit_id');
    }

    /** Convert qty from this unit to target unit. Returns null if no path. */
    public function convertTo(float $qty, int $targetUnitId): ?float
    {
        if ($this->id === $targetUnitId) {
            return $qty;
        }
        $direct = UnitConversion::where('from_unit_id', $this->id)->where('to_unit_id', $targetUnitId)->first();
        if ($direct) {
            return $qty * (float) $direct->factor;
        }
        $reverse = UnitConversion::where('from_unit_id', $targetUnitId)->where('to_unit_id', $this->id)->first();
        if ($reverse && (float) $reverse->factor != 0) {
            return $qty / (float) $reverse->factor;
        }

        return null;
    }
}
