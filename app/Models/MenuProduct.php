<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuProduct extends Model
{
    protected $fillable = ['menu_id', 'product_id', 'qty_per_portion', 'sort_order'];

    protected function casts(): array
    {
        return ['qty_per_portion' => 'decimal:3'];
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
