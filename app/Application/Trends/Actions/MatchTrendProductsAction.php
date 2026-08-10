<?php

namespace App\Application\Trends\Actions;

use App\Domain\Trends\ProductMatcher;
use App\Models\Trend;

class MatchTrendProductsAction
{
    public function __construct(private readonly ProductMatcher $matcher) {}

    /**
     * Encontra e persiste produtos que combinam com um trend
     *
     * @return int quantidade de produtos matched
     */
    public function handle(?int $applicationId = null): int
    {
        $totalMatched = 0;

        Trend::query()
            ->where('status', Trend::STATUS_ACTIVE)
            ->when($applicationId, fn ($query) => $query->where('application_id', $applicationId))
            ->each(function (Trend $trend) use (&$totalMatched): void {
                $matches = $this->matcher->match($trend);
                $totalMatched += $this->matcher->persistMatches($trend, $matches);
            });

        return $totalMatched;
    }

    /**
     * Processa matching para um trend específico
     */
    public function handleOne(Trend $trend): int
    {
        $matches = $this->matcher->match($trend);

        return $this->matcher->persistMatches($trend, $matches);
    }
}
