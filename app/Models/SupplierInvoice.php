<?php

namespace App\Models;

use App\Core\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class SupplierInvoice extends Model
{
    use Auditable;

    protected $fillable = ['organization_id', 'supplier_id', 'purchase_order_id', 'goods_receipt_id', 'supplier_invoice_no', 'number', 'invoice_date', 'due_date', 'subtotal', 'tax_amount', 'grand_total', 'qty_variance', 'price_variance', 'match_status', 'payment_status', 'status', 'notes', 'verified_by'];

    protected function casts(): array
    {
        return [
            'invoice_date' => 'date', 'due_date' => 'date',
            'subtotal' => 'decimal:2', 'tax_amount' => 'decimal:2', 'grand_total' => 'decimal:2',
            'qty_variance' => 'decimal:3', 'price_variance' => 'decimal:2',
        ];
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function goodsReceipt()
    {
        return $this->belongsTo(GoodsReceipt::class);
    }

    public function items()
    {
        return $this->hasMany(SupplierInvoiceItem::class);
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function isMatched(): bool
    {
        return $this->match_status === 'MATCHED';
    }
}
