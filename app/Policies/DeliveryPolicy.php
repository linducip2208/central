<?php

namespace App\Policies;

use App\Models\Delivery;
use App\Models\User;
use App\Policies\Concerns\ChecksOrganization;

class DeliveryPolicy
{
    use ChecksOrganization;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Delivery $model): bool
    {
        return $this->sameOrg($user, $model);
    }

    public function update(User $user, Delivery $model): bool
    {
        return $this->sameOrg($user, $model);
    }

    public function delete(User $user, Delivery $model): bool
    {
        return $this->sameOrg($user, $model) && $this->hasPerm($user, 'admin');
    }
}
