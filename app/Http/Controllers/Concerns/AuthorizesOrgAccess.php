<?php

namespace App\Http\Controllers\Concerns;

use App\Models\CentralKitchen;
use App\Models\School;
use App\Models\Warehouse;

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

    protected function ensureKitchen(int $kitchenId): CentralKitchen
    {
        $kitchen = CentralKitchen::findOrFail($kitchenId);
        $this->ensureOrgAccess($kitchen);

        return $kitchen;
    }

    protected function ensureWarehouse(int $warehouseId): Warehouse
    {
        $warehouse = Warehouse::with('centralKitchen')->findOrFail($warehouseId);
        $user = request()->user();
        if (! ($user && method_exists($user, 'hasRole') && $user->hasRole('super-admin'))) {
            abort_unless($warehouse->centralKitchen && (int) $warehouse->centralKitchen->organization_id === (int) $user->organization_id, 403, 'Gudang di luar organisasi Anda.');
        }

        return $warehouse;
    }

    protected function ensureSchool(int $schoolId): School
    {
        $school = School::findOrFail($schoolId);
        $this->ensureOrgAccess($school);

        return $school;
    }
}
