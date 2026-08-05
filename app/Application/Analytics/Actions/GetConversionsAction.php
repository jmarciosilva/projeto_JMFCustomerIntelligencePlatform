<?php

namespace App\Application\Analytics\Actions;

use App\Domain\Analytics\DateRange;
use App\Models\Application;
use App\Models\Event;

class GetConversionsAction
{
    /**
     * @return array{conversions: int, visitors: int, rate: float}|null Null quando a aplicação não tem `conversion_event_name` configurado.
     */
    public function handle(Application $application, DateRange $range): ?array
    {
        if (! $application->conversion_event_name) {
            return null;
        }

        $conversions = Event::query()
            ->where('application_id', $application->id)
            ->where('event_name', $application->conversion_event_name)
            ->whereBetween('occurred_at', [$range->from, $range->to])
            ->count();

        $visitors = Event::query()
            ->where('application_id', $application->id)
            ->whereBetween('occurred_at', [$range->from, $range->to])
            ->distinct('visitor_id')
            ->count('visitor_id');

        return [
            'conversions' => $conversions,
            'visitors' => $visitors,
            'rate' => $visitors > 0 ? round(($conversions / $visitors) * 100, 1) : 0.0,
        ];
    }
}
