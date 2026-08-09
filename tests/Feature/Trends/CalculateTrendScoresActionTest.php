<?php

use App\Application\Trends\Actions\CalculateTrendScoresAction;
use App\Models\Application;
use App\Models\Trend;
use App\Models\TrendSnapshot;

test('calcula e persiste o score apenas para trends ativas', function () {
    $activeTrend = Trend::factory()->create(['status' => Trend::STATUS_ACTIVE]);
    TrendSnapshot::factory()->for($activeTrend)->create(['mentions' => 10, 'velocity' => 20]);

    $inactiveTrend = Trend::factory()->create(['status' => Trend::STATUS_INACTIVE]);
    TrendSnapshot::factory()->for($inactiveTrend)->create(['mentions' => 10, 'velocity' => 20]);

    $updated = app(CalculateTrendScoresAction::class)->handle();

    expect($updated)->toBe(1);
    expect($activeTrend->fresh()->trend_score)->not->toBeNull();
    expect($inactiveTrend->fresh()->trend_score)->toBeNull();
});

test('trend ativa sem snapshots não é contada como atualizada', function () {
    Trend::factory()->create(['status' => Trend::STATUS_ACTIVE]);

    $updated = app(CalculateTrendScoresAction::class)->handle();

    expect($updated)->toBe(0);
});

test('filtra por application quando informado', function () {
    $applicationA = Application::factory()->create();
    $applicationB = Application::factory()->create();

    $trendA = Trend::factory()->for($applicationA)->create(['status' => Trend::STATUS_ACTIVE]);
    TrendSnapshot::factory()->for($trendA)->create(['mentions' => 10, 'velocity' => 10]);

    $trendB = Trend::factory()->for($applicationB)->create(['status' => Trend::STATUS_ACTIVE]);
    TrendSnapshot::factory()->for($trendB)->create(['mentions' => 10, 'velocity' => 10]);

    $updated = app(CalculateTrendScoresAction::class)->handle($applicationA->id);

    expect($updated)->toBe(1);
    expect($trendA->fresh()->trend_score)->not->toBeNull();
    expect($trendB->fresh()->trend_score)->toBeNull();
});

test('handleOne recalcula uma única trend e retorna sucesso/falha', function () {
    $trend = Trend::factory()->create();
    $action = app(CalculateTrendScoresAction::class);

    expect($action->handleOne($trend))->toBeFalse();

    TrendSnapshot::factory()->for($trend)->create(['mentions' => 5, 'velocity' => 0]);

    expect($action->handleOne($trend))->toBeTrue();
    expect($trend->fresh()->trend_score)->not->toBeNull();
    expect($trend->fresh()->trend_score_computed_at)->not->toBeNull();
});
