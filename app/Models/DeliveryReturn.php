<?php

namespace App\Models;

use App\Core\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class DeliveryReturn extends Model
{
    use Auditable;

    protected $fillable = ['organization_id', 'central_kitchen_id', 'delivery_id', 'warehouse_id', 'product_id', 'batch_id', 'qty', 'reason', 'condition', 'disposition', 'status', 'notes', 'received_by'];

    public function delivery()
    {
        return $this->belongsTo(Delivery::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
}
