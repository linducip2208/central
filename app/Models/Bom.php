<?php

namespace App\Models;

use App\Core\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class Bom extends Model
{
    use Auditable;

    protected $fillable = ['organization_id', 'item_type', 'item_id', 'code', 'version', 'effective_from', 'effective_to', 'yield_qty', 'status', 'notes', 'created_by', 'approved_by', 'approved_at'];

    protected function casts(): array
    {
        return ['effective_from' => 'date', 'effective_to' => 'date', 'yield_qty' => 'decimal:3', 'approved_at' => 'datetime'];
    }

    public function items()
    {
        return $this->hasMany(BomItem::class)->orderBy('level')->orderBy('sort_order');
    }

    public function scopeActive($q, ?string $date = null)
    {
        $date ??= now()->toDateString();

        return $q->where('status', 'ACTIVE')
            ->where(fn ($w) => $w->whereNull('effective_from')->orWhere('effective_from', '<=', $date))
            ->where(fn ($w) => $w->whereNull('effective_to')->orWhere('effective_to', '>=', $date));
    }

    /** BOM aktif terbaru untuk produk (version selection: effective-date + version desc). */
    public static function activeForProduct(int $productId, ?string $date = null): ?self
    {
        return static::where('item_type', 'product')->where('item_id', $productId)
            ->active($date)->orderByDesc('version')->first();
    }
}
