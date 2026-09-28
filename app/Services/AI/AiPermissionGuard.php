<?php

namespace App\Services\AI;

use App\Models\User;

class AiPermissionGuard
{
    /** Topik yang boleh diminta peran tertentu. */
    protected const MATRIX = [
        'demand_forecast' => ['super-admin', 'admin', 'procurement', 'kitchen', 'viewer'],
        'expiry_risk' => ['super-admin', 'admin', 'warehouse', 'kitchen', 'viewer'],
        'waste_analysis' => ['super-admin', 'admin', 'kitchen', 'viewer'],
        'executive_summary' => ['super-admin', 'admin', 'viewer'],
    ];

    public function allowed(User $user, string $topic): bool
    {
        $roles = self::MATRIX[$topic] ?? [];
        if (! $roles) {
            return false;
        }
        if ($user->hasRole(['super-admin', 'admin'])) {
            return true;
        }

        return $user->roles()->whereIn('name', $roles)->exists();
    }
}
