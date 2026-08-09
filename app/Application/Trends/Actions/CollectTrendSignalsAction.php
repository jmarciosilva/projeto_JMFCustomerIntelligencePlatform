<?php

namespace App\Application\Trends\Actions;

use App\Domain\Trends\Contracts\TrendProviderInterface;
use App\Domain\Trends\GoogleTrendsProvider;
use App\Domain\Trends\InstagramTrendProvider;
use App\Domain\Trends\InternalBehaviorProvider;
use App\Domain\Trends\YouTubeTrendProvider;
use App\Models\Trend;
use App\Models\TrendSnapshot;

class CollectTrendSignalsAction
{
    public function __construct(
        private readonly InternalBehaviorProvider $internalBehaviorProvider,
        private readonly InstagramTrendProvider $instagramTrendProvider,
        private readonly GoogleTrendsProvider $googleTrendsProvider,
        private readonly YouTubeTrendProvider $youTubeTrendProvider,
    ) {}

    /**
     * @return int quantidade de snapshots criados
     */
    public function handle(Trend $trend): int
    {
        $created = 0;

        foreach ($this->providers() as $provider) {
            if (! $provider->isConfigured()) {
                continue;
            }

            $data = $provider->collect($trend);

            if ($data === null) {
                continue;
            }

            TrendSnapshot::create([
                'trend_id' => $trend->id,
                'source' => $provider->key(),
                'score' => $data['score'] ?? null,
                'mentions' => $data['mentions'] ?? null,
                'engagement' => $data['engagement'] ?? null,
                'velocity' => $data['velocity'] ?? null,
                'metadata' => $data['metadata'] ?? null,
                'collected_at' => now(),
            ]);

            $created++;
        }

        $trend->update(['last_collected_at' => now()]);

        return $created;
    }

    /**
     * @return list<TrendProviderInterface>
     */
    private function providers(): array
    {
        return [
            $this->internalBehaviorProvider,
            $this->instagramTrendProvider,
            $this->googleTrendsProvider,
            $this->youTubeTrendProvider,
        ];
    }
}
