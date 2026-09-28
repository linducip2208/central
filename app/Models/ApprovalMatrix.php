<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalMatrix extends Model
{
    protected $fillable = ['organization_id', 'approvable_type', 'min_amount', 'level', 'role'];

    protected function casts(): array
    {
        return ['min_amount' => 'decimal:2'];
    }

    /** Level persetujuan yang disyaratkan untuk nominal tertentu. */
    public static function requiredLevel(int $orgId, string $type, float $amount): int
    {
        return (int) (static::where('organization_id', $orgId)->where('approvable_type', $type)
            ->where('min_amount', '<=', $amount)->max('level') ?? 1);
    }
}
