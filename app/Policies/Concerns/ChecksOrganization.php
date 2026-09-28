<?php

namespace App\Policies\Concerns;

use App\Models\User;

trait ChecksOrganization
{
    protected function sameOrg(?User $user, object $model, string $column = 'organization_id'): bool
    {
        if (! $user) {
            return false;
        }
        if (method_exists($user, 'hasRole') && $user->hasRole('super-admin')) {
            return true;
        }
        $org = $model->{$column} ?? $model->centralKitchen?->organization_id;

        return $org && (int) $org === (int) $user->organization_id;
    }

    protected function hasPerm(?User $user, string $permission): bool
    {
        if (! $user) {
            return false;
        }
        if (method_exists($user, 'hasRole') && $user->hasRole(['super-admin', 'admin'])) {
            return true;
        }

        return $user->can($permission);
    }
}
