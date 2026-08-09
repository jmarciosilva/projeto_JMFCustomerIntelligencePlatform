<?php

namespace App\Policies;

use App\Domain\Shared\Enums\Permission;
use App\Models\AffiliateProgram;
use App\Models\User;

class AffiliateProgramPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(Permission::AffiliateProgramsView->value);
    }

    public function view(User $user, AffiliateProgram $program): bool
    {
        return $user->can(Permission::AffiliateProgramsView->value);
    }

    public function create(User $user): bool
    {
        return $user->can(Permission::AffiliateProgramsCreate->value);
    }

    public function update(User $user, AffiliateProgram $program): bool
    {
        return $user->can(Permission::AffiliateProgramsUpdate->value);
    }

    public function delete(User $user, AffiliateProgram $program): bool
    {
        return $user->can(Permission::AffiliateProgramsDelete->value);
    }
}
