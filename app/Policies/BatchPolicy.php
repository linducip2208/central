<?php

namespace App\Policies;

use App\Models\Batch;
use App\Models\User;
use App\Policies\Concerns\ChecksOrganization;

class BatchPolicy
{
    use ChecksOrganization;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Batch $model): bool
    {
        return $this->sameOrg($user, $model);
    }

    public function update(User $user, Batch $model): bool
    {
        return $this->sameOrg($user, $model);
    }

    public function delete(User $user, Batch $model): bool
    {
        return $this->sameOrg($user, $model) && $this->hasPerm($user, 'admin');
    }
}
