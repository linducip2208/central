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

    public function contacts()
    {
        return $this->hasMany(SupplierContact::class);
    }

    public function addresses()
    {
        return $this->hasMany(SupplierAddress::class);
    }

    public function contracts()
    {
        return $this->hasMany(SupplierContract::class);
    }

    public function priceLists()
    {
        return $this->hasMany(SupplierPriceList::class);
    }

    public function scopeActive($q)
    {
        return $q->where('status', 'ACTIVE');
    }
}
