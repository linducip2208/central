<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoodsReceiptItem extends Model
{
    protected $fillable = ['goods_receipt_id', 'purchase_order_item_id', 'ingredient_id', 'qty_ordered', 'qty_received', 'qty_rejected', 'unit_id', 'unit_price', 'batch_no', 'expiry_date', 'production_date', 'qc_status', 'notes'];

    protected function casts(): array
    {
        return [
            'qty_ordered' => 'decimal:3', 'qty_received' => 'decimal:3',
            'qty_rejected' => 'decimal:3', 'unit_price' => 'decimal:2',
            'expiry_date' => 'date', 'production_date' => 'date',
        ];
    }

    public function goodsReceipt()
    {
        return $this->belongsTo(GoodsReceipt::class);
    }

    public function purchaseOrderItem()
    {
        return $this->belongsTo(PurchaseOrderItem::class);
    }

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}
