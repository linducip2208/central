<?php

namespace App\Http\Controllers\Concerns;

trait AuthorizesOrgAccess
{
    /**
     * Isolasi organisasi: user hanya boleh akses data organisasinya sendiri.
     * super-admin melewati batas ini (operator pusat).
     */
    protected function ensureOrgAccess(object $model, string $column = 'organization_id'): void
    {
        $user = request()->user();
        if ($user && method_exists($user, 'hasRole') && $user->hasRole('super-admin')) {
            return;
        }
        $modelOrg = $model->{$column} ?? null;
        // Relasi turunan: centralKitchen → organization
        if ($modelOrg === null && isset($model->centralKitchen)) {
            $modelOrg = $model->centralKitchen?->organization_id;
        }
        abort_unless($modelOrg && $user && (int) $modelOrg === (int) $user->organization_id, 403, 'Data di luar organisasi Anda.');
    }
}
