<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarehouseBin extends Model
{
    protected $fillable = ['warehouse_rack_id', 'code', 'barcode', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function rack()
    {
        return $this->belongsTo(WarehouseRack::class, 'warehouse_rack_id');
    }

    public function batches()
    {
        return $this->hasMany(Batch::class, 'bin_id');
    }

    public function fullCode(): string
    {
        $rack = $this->rack;
        $zone = $rack?->zone;

        return ($zone?->code ?? '?').'-'.($rack?->code ?? '?').'-'.$this->code;
    }
}
