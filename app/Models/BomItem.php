<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BomItem extends Model
{
    protected $fillable = ['bom_id', 'parent_bom_item_id', 'component_type', 'component_id', 'qty', 'unit_id', 'scrap_pct', 'waste_pct', 'level', 'sort_order', 'notes'];

    protected function casts(): array
    {
        return ['qty' => 'decimal:4', 'scrap_pct' => 'decimal:2', 'waste_pct' => 'decimal:2'];
    }

    public function bom()
    {
        return $this->belongsTo(Bom::class);
    }

    public function parent()
    {
        return $this->belongsTo(BomItem::class, 'parent_bom_item_id');
    }

    public function children()
    {
        return $this->hasMany(BomItem::class, 'parent_bom_item_id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function effectiveQty(): float
    {
        return (float) $this->qty * (1 + (float) $this->scrap_pct / 100) * (1 + (float) $this->waste_pct / 100);
    }
}
