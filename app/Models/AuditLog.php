<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = ['organization_id', 'user_id', 'action', 'model_type', 'model_id', 'old_data', 'new_data', 'ip_address', 'user_agent'];

    protected function casts(): array
    {
        return ['old_data' => 'array', 'new_data' => 'array'];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForModel($q, string $type, $id)
    {
        return $q->where('model_type', $type)->where('model_id', $id);
    }
}
