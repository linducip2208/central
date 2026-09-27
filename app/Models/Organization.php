<?php

namespace App\Models;

use App\Core\Traits\Auditable;
use App\Core\Traits\HasSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organization extends Model
{
    use Auditable, HasSlug, SoftDeletes;

    protected $fillable = ['code', 'name', 'slug', 'email', 'phone', 'address', 'city', 'province', 'logo', 'status'];

    public function centralKitchens()
    {
        return $this->hasMany(CentralKitchen::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function suppliers()
    {
        return $this->hasMany(Supplier::class);
    }

    public function schools()
    {
        return $this->hasMany(School::class);
    }

    public function scopeActive($q)
    {
        return $q->where('status', 'ACTIVE');
    }
}
