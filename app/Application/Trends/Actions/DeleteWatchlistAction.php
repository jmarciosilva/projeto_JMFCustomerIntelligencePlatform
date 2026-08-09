<?php

namespace App\Application\Trends\Actions;

use App\Models\Watchlist;
use App\Support\Audit\AuditLogger;
use Illuminate\Validation\ValidationException;

class DeleteWatchlistAction
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function handle(Watchlist $watchlist): void
    {
        if ($watchlist->trends()->exists()) {
            throw ValidationException::withMessages([
                'watchlist' => 'Não é possível excluir uma watchlist que possui tendências vinculadas.',
            ]);
        }

        $this->auditLogger->log('watchlist.deleted', $watchlist, [
            'name' => $watchlist->name,
        ]);

        $watchlist->delete();
    }
}
