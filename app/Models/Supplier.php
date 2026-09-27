<?php

namespace App\Models;

use App\Core\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use Auditable, SoftDeletes;

    protected $fillable = ['organization_id', 'code', 'name', 'slug', 'category', 'contact_person', 'phone', 'email', 'address', 'tax_number', 'bank_account', 'rating', 'status'];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function scopeActive($q)
    {
        return $q->where('status', 'ACTIVE');
    }
}
