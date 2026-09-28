<?php

namespace App\Policies;

use App\Models\Recall;
use App\Models\User;
use App\Policies\Concerns\ChecksOrganization;

class RecallPolicy
{
    use ChecksOrganization;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Recall $model): bool
    {
        return $this->sameOrg($user, $model);
    }

    public function update(User $user, Recall $model): bool
    {
        return $this->sameOrg($user, $model);
    }

    public function delete(User $user, Recall $model): bool
    {
        return $this->sameOrg($user, $model) && $this->hasPerm($user, 'admin');
    }
}
