<?php

namespace App\Policies;

use App\Models\SupplierInvoice;
use App\Models\User;
use App\Policies\Concerns\ChecksOrganization;

class SupplierInvoicePolicy
{
    use ChecksOrganization;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, SupplierInvoice $model): bool
    {
        return $this->sameOrg($user, $model);
    }

    public function update(User $user, SupplierInvoice $model): bool
    {
        return $this->sameOrg($user, $model);
    }

    public function delete(User $user, SupplierInvoice $model): bool
    {
        return $this->sameOrg($user, $model) && $this->hasPerm($user, 'admin');
    }
}
