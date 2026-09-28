<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecallItem extends Model
{
    protected $fillable = ['recall_id', 'batch_id', 'stock_on_hand', 'qty_delivered', 'action'];

    protected function casts(): array
    {
        return ['stock_on_hand' => 'decimal:3', 'qty_delivered' => 'decimal:3'];
    }

    public function recall()
    {
        return $this->belongsTo(Recall::class);
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }
}
