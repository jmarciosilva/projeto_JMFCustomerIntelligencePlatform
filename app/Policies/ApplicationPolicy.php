<?php

namespace App\Policies;

use App\Domain\Shared\Enums\Permission;
use App\Models\Application;
use App\Models\User;

class ApplicationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(Permission::ApplicationsView->value);
    }

    public function view(User $user, Application $application): bool
    {
        return $user->can(Permission::ApplicationsView->value);
    }

    public function create(User $user): bool
    {
        return $user->can(Permission::ApplicationsCreate->value);
    }

    public function update(User $user, Application $application): bool
    {
        return $user->can(Permission::ApplicationsUpdate->value);
    }

    public function delete(User $user, Application $application): bool
    {
        return $user->can(Permission::ApplicationsDelete->value);
    }

    public function manageTokens(User $user, Application $application): bool
    {
        return $user->can(Permission::ApplicationsTokensManage->value);
    }
}
