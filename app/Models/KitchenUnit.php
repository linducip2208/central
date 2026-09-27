<?php

namespace App\Models;

use App\Core\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KitchenUnit extends Model
{
    use Auditable, SoftDeletes;

    protected $fillable = ['central_kitchen_id', 'code', 'name', 'unit_type', 'capacity', 'status'];

    public function centralKitchen()
    {
        return $this->belongsTo(CentralKitchen::class);
    }
}
