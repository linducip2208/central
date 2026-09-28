<?php

namespace App\Policies;

use App\Models\ProductionOrder;
use App\Models\User;
use App\Policies\Concerns\ChecksOrganization;

class ProductionOrderPolicy
{
    use ChecksOrganization;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, ProductionOrder $model): bool
    {
        return $this->sameOrg($user, $model);
    }

    public function update(User $user, ProductionOrder $model): bool
    {
        return $this->sameOrg($user, $model);
    }

    public function delete(User $user, ProductionOrder $model): bool
    {
        return $this->sameOrg($user, $model) && $this->hasPerm($user, 'admin');
    }
}
