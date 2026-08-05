<?php

namespace App\Application\Analytics\Actions;

use App\Domain\Analytics\DateRange;
use App\Models\Application;
use App\Models\DailyMetric;
use App\Models\Event;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class GetDashboardOverviewAction
{
    /**
     * @return array{totals: array<string, int|null>, trend: list<array<string, mixed>>}
     */
    public function handle(Application $application, DateRange $range): array
    {
        $totals = $this->liveTotals($application, $range->from, $range->to);

        $trend = [];

        foreach ($range->dates() as $date) {
            $dayTotals = $date->isToday()
                ? $this->liveTotals($application, $date->clone()->startOfDay(), $date->clone()->endOfDay())
                : $this->storedTotals($application, $date);

            $trend[] = array_merge(['date' => $date->toDateString()], $dayTotals);
        }

        return [
            'totals' => $totals,
            'trend' => $trend,
        ];
    }

    /**
     * @return array<string, int|null>
     */
    private function liveTotals(Application $application, Carbon $from, Carbon $to): array
    {
        return [
            'events_total' => $this->baseQuery($application, $from, $to)->count(),
            'visitors_unique' => $this->baseQuery($application, $from, $to)->distinct('visitor_id')->count('visitor_id'),
            'sessions_unique' => $this->baseQuery($application, $from, $to)->whereNotNull('session_id')->distinct('session_id')->count('session_id'),
            'conversions_total' => $application->conversion_event_name
                ? $this->baseQuery($application, $from, $to)->where('event_name', $application->conversion_event_name)->count()
                : null,
        ];
    }

    /**
     * @return array<string, int|null>
     */
    private function storedTotals(Application $application, Carbon $date): array
    {
        $rows = DailyMetric::query()
            ->where('application_id', $application->id)
            ->whereDate('date', $date->toDateString())
            ->pluck('metric_value', 'metric_key');

        return [
            'events_total' => (int) ($rows['events_total'] ?? 0),
            'visitors_unique' => (int) ($rows['visitors_unique'] ?? 0),
            'sessions_unique' => (int) ($rows['sessions_unique'] ?? 0),
            'conversions_total' => $application->conversion_event_name
                ? (int) ($rows['conversions_total'] ?? 0)
                : null,
        ];
    }

    /**
     * @return Builder<Event>
     */
    private function baseQuery(Application $application, Carbon $from, Carbon $to): Builder
    {
        return Event::query()
            ->where('application_id', $application->id)
            ->whereBetween('occurred_at', [$from, $to]);
    }
}
