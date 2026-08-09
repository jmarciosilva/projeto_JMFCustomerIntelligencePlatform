<?php

namespace App\Application\Trends\Actions;

use App\Domain\Trends\WatchlistTrendSynchronizer;
use App\Models\Application;
use App\Models\Watchlist;
use App\Support\Audit\AuditLogger;

class CreateWatchlistAction
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly WatchlistTrendSynchronizer $synchronizer,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(Application $application, array $data): Watchlist
    {
        $watchlist = Watchlist::create([
            'application_id' => $application->id,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'keywords' => $data['keywords'] ?? [],
            'hashtags' => $data['hashtags'] ?? [],
            'categories' => $data['categories'] ?? [],
            'collection_frequency' => $data['collection_frequency'] ?? Watchlist::FREQUENCY_DAILY,
            'status' => $data['status'] ?? Watchlist::STATUS_ACTIVE,
        ]);

        $this->synchronizer->sync($watchlist);

        $this->auditLogger->log('watchlist.created', $watchlist, [
            'name' => $watchlist->name,
            'keywords' => $watchlist->keywords,
            'hashtags' => $watchlist->hashtags,
        ]);

        return $watchlist;
    }
}
