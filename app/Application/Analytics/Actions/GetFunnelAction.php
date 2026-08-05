<?php

namespace App\Application\Analytics\Actions;

use App\Domain\Analytics\DateRange;
use App\Models\Application;
use App\Models\Event;
use Illuminate\Support\Collection;

class GetFunnelAction
{
    /**
     * Funil estrito: cada etapa só conta visitantes que também alcançaram a
     * etapa anterior (interseção de conjuntos de visitor_id), não apenas a
     * contagem solta de quem disparou aquele evento em algum momento.
     *
     * @param  list<string>  $steps  Lista ordenada de event_name.
     * @return list<array{event_name: string, visitors: int, conversion_rate: float}>
     */
    public function handle(Application $application, DateRange $range, array $steps): array
    {
        $result = [];
        $carryVisitors = null;
        $previousCount = null;

        foreach ($steps as $eventName) {
            $visitors = Event::query()
                ->where('application_id', $application->id)
                ->where('event_name', $eventName)
                ->whereBetween('occurred_at', [$range->from, $range->to])
                ->distinct()
                ->pluck('visitor_id')
                ->unique()
                ->values();

            if ($carryVisitors instanceof Collection) {
                $visitors = $visitors->intersect($carryVisitors)->values();
            }

            $count = $visitors->count();

            $result[] = [
                'event_name' => $eventName,
                'visitors' => $count,
                'conversion_rate' => match (true) {
                    $previousCount === null => 100.0,
                    $previousCount === 0 => 0.0,
                    default => round(($count / $previousCount) * 100, 1),
                },
            ];

            $carryVisitors = $visitors;
            $previousCount = $count;
        }

        return $result;
    }
}
