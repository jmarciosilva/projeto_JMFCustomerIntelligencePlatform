<?php

namespace App\Domain\Trends;

use App\Domain\Trends\Contracts\TrendProviderInterface;
use App\Models\Event;
use App\Models\Trend;
use App\Models\TrendSnapshot;
use Carbon\Carbon;

/**
 * Único provider "real" automático do MVP: reaproveita dados de comportamento
 * já coletados pela própria plataforma (eventos `product.search` e
 * `product.viewed` do marketplace, Fase 12) como sinal de interesse — dado
 * próprio, sem depender de nenhuma API externa.
 */
class InternalBehaviorProvider implements TrendProviderInterface
{
    private const WINDOW_DAYS = 7;

    public function key(): string
    {
        return TrendSnapshot::SOURCE_INTERNAL_BEHAVIOR;
    }

    public function isConfigured(): bool
    {
        return true;
    }

    public function collect(Trend $trend): ?array
    {
        $term = trim($trend->term);

        if ($term === '') {
            return null;
        }

        $now = now();
        $currentCount = $this->countMatches(
            $trend->application_id,
            $term,
            $now->copy()->subDays(self::WINDOW_DAYS),
            $now,
        );
        $previousCount = $this->countMatches(
            $trend->application_id,
            $term,
            $now->copy()->subDays(self::WINDOW_DAYS * 2),
            $now->copy()->subDays(self::WINDOW_DAYS),
        );

        $velocity = match (true) {
            $previousCount > 0 => round((($currentCount - $previousCount) / $previousCount) * 100, 2),
            $currentCount > 0 => 100.0,
            default => null,
        };

        return [
            'mentions' => $currentCount,
            'engagement' => null,
            'velocity' => $velocity,
            'score' => null,
            'metadata' => [
                'window_days' => self::WINDOW_DAYS,
                'previous_mentions' => $previousCount,
            ],
        ];
    }

    private function countMatches(int $applicationId, string $term, Carbon $from, Carbon $to): int
    {
        return Event::query()
            ->where('application_id', $applicationId)
            ->whereBetween('occurred_at', [$from, $to])
            ->where(function ($query) use ($term): void {
                $query->where(function ($subQuery) use ($term): void {
                    $subQuery->where('event_name', 'product.search')
                        ->where('properties->search_term', 'like', "%{$term}%");
                })->orWhere(function ($subQuery) use ($term): void {
                    $subQuery->where('event_name', 'product.viewed')
                        ->where('properties->category', 'like', "%{$term}%");
                });
            })
            ->count();
    }
}
