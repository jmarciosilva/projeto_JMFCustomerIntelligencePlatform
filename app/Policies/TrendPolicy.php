<?php

namespace App\Policies;

use App\Domain\Shared\Enums\Permission;
use App\Models\Trend;
use App\Models\User;

/**
 * Trends não são criados/excluídos diretamente pelo usuário — são derivados
 * das palavras-chave/hashtags de uma Watchlist (ver
 * `App\Domain\Trends\WatchlistTrendSynchronizer`) — por isso reaproveita as
 * mesmas permissions `watchlists.*`, sem duplicar um novo conjunto de
 * permissions só para visualização/registro manual de observações.
 */
class TrendPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(Permission::WatchlistsView->value);
    }

    public function view(User $user, Trend $trend): bool
    {
        return $user->can(Permission::WatchlistsView->value);
    }

    public function update(User $user, Trend $trend): bool
    {
        return $user->can(Permission::WatchlistsUpdate->value);
    }
}
