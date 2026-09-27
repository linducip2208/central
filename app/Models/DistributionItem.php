<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DistributionItem extends Model
{
    protected $fillable = ['distribution_id', 'school_id', 'product_id', 'qty_planned', 'qty_delivered'];

    public function distribution()
    {
        return $this->belongsTo(Distribution::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
