<?php

namespace App\Policies;

use App\Models\PurchaseOrder;
use App\Models\User;
use App\Policies\Concerns\ChecksOrganization;

class PurchaseOrderPolicy
{
    use ChecksOrganization;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, PurchaseOrder $model): bool
    {
        return $this->sameOrg($user, $model);
    }

    public function update(User $user, PurchaseOrder $model): bool
    {
        return $this->sameOrg($user, $model);
    }

    public function delete(User $user, PurchaseOrder $model): bool
    {
        return $this->sameOrg($user, $model) && $this->hasPerm($user, 'admin');
    }
}
