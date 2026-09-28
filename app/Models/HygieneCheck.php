<?php

namespace App\Models;

use App\Core\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class HygieneCheck extends Model
{
    use Auditable;

    protected $fillable = ['organization_id', 'central_kitchen_id', 'check_type', 'area', 'items', 'photo_path', 'result', 'notes', 'checked_by', 'checked_at'];

    protected function casts(): array
    {
        return ['items' => 'array', 'checked_at' => 'datetime'];
    }

    public function checker()
    {
        return $this->belongsTo(User::class, 'checked_by');
    }
}
