<?php

namespace App\Application\Trends\Actions;

use App\Models\Trend;
use App\Models\TrendSnapshot;

class RegisterManualTrendSnapshotAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(Trend $trend, array $data): TrendSnapshot
    {
        $snapshot = TrendSnapshot::create([
            'trend_id' => $trend->id,
            'source' => TrendSnapshot::SOURCE_MANUAL,
            'score' => $data['score'] ?? null,
            'mentions' => $data['mentions'] ?? null,
            'engagement' => $data['engagement'] ?? null,
            'velocity' => $data['velocity'] ?? null,
            'metadata' => $data['notes'] ?? null ? ['notes' => $data['notes']] : null,
            'collected_at' => now(),
        ]);

        $trend->update(['last_collected_at' => now()]);

        return $snapshot;
    }
}
