<?php

namespace App\Policies;

use App\Models\GoodsReceipt;
use App\Models\User;
use App\Policies\Concerns\ChecksOrganization;

class GoodsReceiptPolicy
{
    use ChecksOrganization;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, GoodsReceipt $model): bool
    {
        return $this->sameOrg($user, $model);
    }

    public function update(User $user, GoodsReceipt $model): bool
    {
        return $this->sameOrg($user, $model);
    }

    public function delete(User $user, GoodsReceipt $model): bool
    {
        return $this->sameOrg($user, $model) && $this->hasPerm($user, 'admin');
    }
}
