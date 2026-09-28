<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalDelegation extends Model
{
    protected $fillable = ['organization_id', 'delegator_id', 'delegate_id', 'start_date', 'end_date', 'is_active'];

    protected function casts(): array
    {
        return ['start_date' => 'date', 'end_date' => 'date', 'is_active' => 'boolean'];
    }

    public function delegator()
    {
        return $this->belongsTo(User::class, 'delegator_id');
    }

    public function delegate()
    {
        return $this->belongsTo(User::class, 'delegate_id');
    }

    public function scopeEffective($q, ?string $date = null)
    {
        $date ??= now()->toDateString();

        return $q->where('is_active', true)->whereDate('start_date', '<=', $date)->whereDate('end_date', '>=', $date);
    }

    public static function delegateFor(int $userId, ?string $date = null): ?int
    {
        return static::effective($date)->where('delegator_id', $userId)->value('delegate_id');
    }
}
