<?php

namespace App\Models;

use App\Core\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class SupplierReturn extends Model
{
    use Auditable;

    protected $fillable = ['organization_id', 'central_kitchen_id', 'warehouse_id', 'goods_receipt_id', 'supplier_id', 'ingredient_id', 'batch_id', 'number', 'qty', 'reason', 'status', 'notes', 'created_by'];

    protected function casts(): array
    {
        return ['qty' => 'decimal:3'];
    }

    public function goodsReceipt()
    {
        return $this->belongsTo(GoodsReceipt::class);
    }

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }
}
