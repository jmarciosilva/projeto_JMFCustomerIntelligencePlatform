<?php

namespace App\Application\Analytics\Actions;

use App\Domain\Analytics\DateRange;
use App\Models\Application;
use App\Models\Event;

class GetTopPagesAction
{
    /**
     * @return list<array{page_url: string, total: int}>
     */
    public function handle(Application $application, DateRange $range, int $limit = 10): array
    {
        return Event::query()
            ->where('application_id', $application->id)
            ->where('event_name', 'page.viewed')
            ->whereBetween('occurred_at', [$range->from, $range->to])
            ->whereRaw('context->>\'$.page_url\' IS NOT NULL')
            ->selectRaw('context->>\'$.page_url\' AS page_url, COUNT(*) AS total')
            ->groupBy('page_url')
            ->orderByDesc('total')
            ->limit($limit)
            ->get()
            ->map(fn ($row) => ['page_url' => $row['page_url'], 'total' => (int) $row['total']])
            ->all();
    }
}
