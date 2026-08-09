<?php

namespace App\Policies;

use App\Domain\Shared\Enums\Permission;
use App\Models\AffiliateProduct;
use App\Models\User;

class AffiliateProductPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(Permission::AffiliateProductsView->value);
    }

    public function view(User $user, AffiliateProduct $product): bool
    {
        return $user->can(Permission::AffiliateProductsView->value);
    }

    public function create(User $user): bool
    {
        return $user->can(Permission::AffiliateProductsCreate->value);
    }

    public function update(User $user, AffiliateProduct $product): bool
    {
        return $user->can(Permission::AffiliateProductsUpdate->value);
    }

    public function delete(User $user, AffiliateProduct $product): bool
    {
        return $user->can(Permission::AffiliateProductsDelete->value);
    }
}
