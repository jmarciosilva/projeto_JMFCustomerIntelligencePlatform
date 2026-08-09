<?php

namespace App\Application\Trends\Actions;

use App\Domain\Trends\WatchlistTrendSynchronizer;
use App\Models\Watchlist;
use App\Support\Audit\AuditLogger;

class UpdateWatchlistAction
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly WatchlistTrendSynchronizer $synchronizer,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(Watchlist $watchlist, array $data): Watchlist
    {
        $before = $watchlist->only(['name', 'keywords', 'hashtags', 'status']);

        $watchlist->fill([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'keywords' => $data['keywords'] ?? [],
            'hashtags' => $data['hashtags'] ?? [],
            'categories' => $data['categories'] ?? [],
            'collection_frequency' => $data['collection_frequency'] ?? $watchlist->collection_frequency,
            'status' => $data['status'] ?? $watchlist->status,
        ]);
        $watchlist->save();

        $this->synchronizer->sync($watchlist);

        $this->auditLogger->log('watchlist.updated', $watchlist, [
            'before' => $before,
            'after' => $watchlist->only(['name', 'keywords', 'hashtags', 'status']),
        ]);

        return $watchlist;
    }
}
