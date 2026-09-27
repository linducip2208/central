<?php

namespace App\Models;

use App\Core\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
{
    use Auditable, SoftDeletes;

    protected $fillable = ['organization_id', 'central_kitchen_id', 'supplier_id', 'warehouse_id', 'purchase_request_id', 'number', 'order_date', 'expected_date', 'subtotal', 'tax_amount', 'discount_amount', 'grand_total', 'payment_terms', 'status', 'notes', 'ordered_by', 'approved_by', 'approved_at', 'reject_reason'];

    protected function casts(): array
    {
        return [
            'order_date' => 'date', 'expected_date' => 'date', 'approved_at' => 'datetime',
            'subtotal' => 'decimal:2', 'tax_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2', 'grand_total' => 'decimal:2',
        ];
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function purchaseRequest()
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function receipts()
    {
        return $this->hasMany(GoodsReceipt::class);
    }

    public function recalculateTotals(): void
    {
        $subtotal = $this->items()->sum('line_total');
        $this->subtotal = $subtotal;
        $this->grand_total = $subtotal + (float) $this->tax_amount - (float) $this->discount_amount;
        $this->save();
    }

    public function isFullyReceived(): bool
    {
        return $this->items()->whereRaw('qty_received < qty_ordered')->doesntExist();
    }

    public function refreshReceiveStatus(): void
    {
        if ($this->isFullyReceived()) {
            $this->status = 'COMPLETED';
        } elseif ($this->items()->where('qty_received', '>', 0)->exists()) {
            $this->status = 'PARTIAL';
        }
        $this->save();
    }
}
