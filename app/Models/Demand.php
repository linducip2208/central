<?php

namespace App\Models;

use App\Core\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Demand extends Model
{
    use Auditable, SoftDeletes;

    protected $fillable = ['organization_id', 'central_kitchen_id', 'school_id', 'menu_id', 'product_id', 'code', 'demand_date', 'portions', 'qty', 'source', 'status', 'notes', 'created_by'];

    protected function casts(): array
    {
        return ['demand_date' => 'date', 'qty' => 'decimal:3'];
    }

    public function centralKitchen()
    {
        return $this->belongsTo(CentralKitchen::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class);
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
