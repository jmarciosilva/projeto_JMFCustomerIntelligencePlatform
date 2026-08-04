<?php

namespace App\Policies;

use App\Domain\Shared\Enums\Permission;
use App\Models\Tenant;
use App\Models\User;

class TenantPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(Permission::TenantsView->value);
    }

    public function view(User $user, Tenant $tenant): bool
    {
        return $user->can(Permission::TenantsView->value);
    }

    public function create(User $user): bool
    {
        return $user->can(Permission::TenantsCreate->value);
    }

    public function update(User $user, Tenant $tenant): bool
    {
        return $user->can(Permission::TenantsUpdate->value);
    }

    public function delete(User $user, Tenant $tenant): bool
    {
        return $user->can(Permission::TenantsDelete->value);
    }
}
