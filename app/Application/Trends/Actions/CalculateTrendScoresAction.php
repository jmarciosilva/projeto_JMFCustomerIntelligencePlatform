<?php

namespace App\Application\Trends\Actions;

use App\Domain\Trends\TrendScoreCalculator;
use App\Models\Trend;

class CalculateTrendScoresAction
{
    public function __construct(private readonly TrendScoreCalculator $calculator) {}

    /**
     * @return int quantidade de trends com score recalculado
     */
    public function handle(?int $applicationId = null): int
    {
        $updated = 0;

        Trend::query()
            ->where('status', Trend::STATUS_ACTIVE)
            ->when($applicationId, fn ($query) => $query->where('application_id', $applicationId))
            ->each(function (Trend $trend) use (&$updated): void {
                if ($this->handleOne($trend)) {
                    $updated++;
                }
            });

        return $updated;
    }

    public function handleOne(Trend $trend): bool
    {
        $result = $this->calculator->calculate($trend);

        if ($result === null) {
            return false;
        }

        $trend->update([
            'trend_score' => $result['score'],
            'trend_score_breakdown' => $result['breakdown'],
            'trend_score_computed_at' => now(),
        ]);

        return true;
    }
}
