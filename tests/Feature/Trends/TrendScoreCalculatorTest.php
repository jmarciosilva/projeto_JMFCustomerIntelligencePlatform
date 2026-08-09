<?php

use App\Domain\Trends\TrendScoreCalculator;
use App\Models\Trend;
use App\Models\TrendSnapshot;

function createTrendSnapshotsForScore(Trend $trend, array $mentionsByDay, array $velocityByDay, ?int $engagement = null): void
{
    $daysAgo = count($mentionsByDay) - 1;

    foreach ($mentionsByDay as $index => $mentions) {
        TrendSnapshot::factory()->for($trend)->create([
            'mentions' => $mentions,
            'velocity' => $velocityByDay[$index],
            'engagement' => $engagement,
            'collected_at' => now()->subDays($daysAgo - $index),
        ]);
    }
}

test('trend sem nenhum snapshot não tem score calculável', function () {
    $trend = Trend::factory()->create();

    expect(app(TrendScoreCalculator::class)->calculate($trend))->toBeNull();
});

test('tendência em alta e consistente gera score alto', function () {
    $trend = Trend::factory()->create();

    createTrendSnapshotsForScore(
        $trend,
        mentionsByDay: [40, 45, 50, 55, 60],
        velocityByDay: [10, 10, 10, 10, 20],
    );

    $result = app(TrendScoreCalculator::class)->calculate($trend);

    expect($result)->not->toBeNull();
    expect($result['score'])->toBe(86.67);
    expect($result['breakdown']['factors'])->not->toHaveKey('engagement');
    expect($result['breakdown']['snapshots_considered'])->toBe(5);
});

test('tendência em queda erratica gera score baixo', function () {
    $trend = Trend::factory()->create();

    createTrendSnapshotsForScore(
        $trend,
        mentionsByDay: [5, 3, 0, 0, 0],
        velocityByDay: [-20, -30, -40, -50, -60],
    );

    $result = app(TrendScoreCalculator::class)->calculate($trend);

    expect($result['score'])->toBe(16.44);
});

test('engajamento disponível entra no cálculo e some do breakdown quando ausente', function () {
    $trendWithEngagement = Trend::factory()->create();
    createTrendSnapshotsForScore($trendWithEngagement, [40, 45, 50, 55, 60], [10, 10, 10, 10, 20], engagement: 200);

    $trendWithoutEngagement = Trend::factory()->create();
    createTrendSnapshotsForScore($trendWithoutEngagement, [40, 45, 50, 55, 60], [10, 10, 10, 10, 20]);

    $calculator = app(TrendScoreCalculator::class);
    $withEngagement = $calculator->calculate($trendWithEngagement);
    $withoutEngagement = $calculator->calculate($trendWithoutEngagement);

    expect($withEngagement['breakdown']['factors'])->toHaveKey('engagement');
    expect($withEngagement['breakdown']['factors']['engagement'])->toBe(100.0);
    expect($withoutEngagement['breakdown']['factors'])->not->toHaveKey('engagement');
    // Engajamento máximo (100) é maior que os outros fatores combinados, então incluí-lo eleva o score.
    expect($withEngagement['score'])->toBeGreaterThan($withoutEngagement['score']);
});

test('trend com apenas um snapshot ainda calcula um score (dados limitados)', function () {
    $trend = Trend::factory()->create();
    TrendSnapshot::factory()->for($trend)->create(['mentions' => 10, 'velocity' => 0, 'engagement' => null]);

    $result = app(TrendScoreCalculator::class)->calculate($trend);

    expect($result)->not->toBeNull();
    expect($result['breakdown']['snapshots_considered'])->toBe(1);
});

test('considera apenas os últimos 5 snapshots (janela)', function () {
    $trend = Trend::factory()->create();

    // 3 snapshots antigos, ruins, fora da janela.
    createTrendSnapshotsForScoreOld($trend, 3);

    createTrendSnapshotsForScore($trend, [40, 45, 50, 55, 60], [10, 10, 10, 10, 20]);

    $result = app(TrendScoreCalculator::class)->calculate($trend);

    expect($result['breakdown']['snapshots_considered'])->toBe(5);
    expect($result['score'])->toBe(86.67);
});

function createTrendSnapshotsForScoreOld(Trend $trend, int $count): void
{
    for ($i = 0; $i < $count; $i++) {
        TrendSnapshot::factory()->for($trend)->create([
            'mentions' => 0,
            'velocity' => -90,
            'engagement' => null,
            'collected_at' => now()->subDays(100 + $i),
        ]);
    }
}
