<?php

namespace App\Application\Analytics\Actions;

use App\Domain\Analytics\DateRange;
use App\Models\Application;
use App\Models\Event;

class GetUtmBreakdownAction
{
    /**
     * @return list<array{utm_source: string, utm_medium: ?string, utm_campaign: ?string, total: int}>
     */
    public function handle(Application $application, DateRange $range, int $limit = 20): array
    {
        return Event::query()
            ->where('application_id', $application->id)
            ->whereBetween('occurred_at', [$range->from, $range->to])
            ->whereRaw('context->>\'$.utm_source\' IS NOT NULL')
            ->selectRaw(<<<'SQL'
                context->>'$.utm_source' AS utm_source,
                context->>'$.utm_medium' AS utm_medium,
                context->>'$.utm_campaign' AS utm_campaign,
                COUNT(*) AS total
                SQL)
            ->groupBy('utm_source', 'utm_medium', 'utm_campaign')
            ->orderByDesc('total')
            ->limit($limit)
            ->get()
            ->map(fn ($row) => [
                'utm_source' => $row['utm_source'],
                'utm_medium' => $row['utm_medium'],
                'utm_campaign' => $row['utm_campaign'],
                'total' => (int) $row['total'],
            ])
            ->all();
    }
}
