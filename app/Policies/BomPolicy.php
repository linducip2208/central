<?php

namespace App\Policies;

use App\Models\Bom;
use App\Models\User;
use App\Policies\Concerns\ChecksOrganization;

class BomPolicy
{
    use ChecksOrganization;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Bom $model): bool
    {
        return $this->sameOrg($user, $model);
    }

    public function update(User $user, Bom $model): bool
    {
        return $this->sameOrg($user, $model);
    }

    public function delete(User $user, Bom $model): bool
    {
        return $this->sameOrg($user, $model) && $this->hasPerm($user, 'admin');
    }
}
