<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PackagingItem extends Model
{
    protected $fillable = ['packaging_id', 'product_id', 'qty_packed', 'batch_id'];

    public function packaging()
    {
        return $this->belongsTo(Packaging::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }
}
