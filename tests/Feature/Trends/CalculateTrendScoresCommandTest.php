<?php

use App\Models\Trend;
use App\Models\TrendSnapshot;

test('comando recalcula o score de todas as trends ativas com snapshots', function () {
    $trend = Trend::factory()->create(['status' => Trend::STATUS_ACTIVE]);
    TrendSnapshot::factory()->for($trend)->create(['mentions' => 10, 'velocity' => 10]);

    $this->artisan('trends:calculate-scores')
        ->expectsOutputToContain('1 tendência')
        ->assertSuccessful();

    expect($trend->fresh()->trend_score)->not->toBeNull();
});
