<?php

use App\Application\Trends\Actions\CollectTrendSignalsAction;
use App\Application\Trends\Actions\RegisterManualTrendSnapshotAction;
use App\Models\Application;
use App\Models\Event;
use App\Models\Trend;
use App\Models\TrendSnapshot;

test('collect cria snapshot apenas a partir de providers configurados', function () {
    $application = Application::factory()->create();
    $trend = Trend::factory()->for($application)->create(['term' => 'cafeteira']);

    Event::factory()->for($application)->create([
        'event_name' => 'product.search',
        'properties' => ['search_term' => 'cafeteira espresso'],
        'occurred_at' => now()->subDay(),
    ]);

    $created = app(CollectTrendSignalsAction::class)->handle($trend);

    expect($created)->toBe(1);
    $this->assertDatabaseHas('trend_snapshots', [
        'trend_id' => $trend->id,
        'source' => TrendSnapshot::SOURCE_INTERNAL_BEHAVIOR,
        'mentions' => 1,
    ]);
    $this->assertDatabaseMissing('trend_snapshots', ['source' => TrendSnapshot::SOURCE_INSTAGRAM]);
    $this->assertDatabaseMissing('trend_snapshots', ['source' => TrendSnapshot::SOURCE_GOOGLE_TRENDS]);
    $this->assertDatabaseMissing('trend_snapshots', ['source' => TrendSnapshot::SOURCE_YOUTUBE]);

    expect($trend->fresh()->last_collected_at)->not->toBeNull();
});

test('registrar observação manual cria snapshot com origem manual', function () {
    $trend = Trend::factory()->create();

    $snapshot = app(RegisterManualTrendSnapshotAction::class)->handle($trend, [
        'mentions' => 42,
        'engagement' => 10,
        'velocity' => 5.5,
        'score' => 70,
        'notes' => 'Visto 42 posts no Instagram hoje.',
    ]);

    expect($snapshot->source)->toBe(TrendSnapshot::SOURCE_MANUAL);
    expect($snapshot->mentions)->toBe(42);
    expect($snapshot->metadata)->toBe(['notes' => 'Visto 42 posts no Instagram hoje.']);
    expect($trend->fresh()->last_collected_at)->not->toBeNull();
});
