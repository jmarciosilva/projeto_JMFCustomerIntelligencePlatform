<?php

namespace App\Policies;

use App\Domain\Shared\Enums\Permission;
use App\Models\User;
use App\Models\Watchlist;

class WatchlistPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(Permission::WatchlistsView->value);
    }

    public function view(User $user, Watchlist $watchlist): bool
    {
        return $user->can(Permission::WatchlistsView->value);
    }

    public function create(User $user): bool
    {
        return $user->can(Permission::WatchlistsCreate->value);
    }

    public function update(User $user, Watchlist $watchlist): bool
    {
        return $user->can(Permission::WatchlistsUpdate->value);
    }

    public function delete(User $user, Watchlist $watchlist): bool
    {
        return $user->can(Permission::WatchlistsDelete->value);
    }
}
