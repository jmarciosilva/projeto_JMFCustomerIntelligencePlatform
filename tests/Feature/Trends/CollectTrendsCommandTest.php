<?php

use App\Application\Trends\Actions\CollectTrendSignalsAction;
use App\Jobs\CollectTrendSignalsJob;
use App\Models\Trend;
use App\Models\Watchlist;
use Illuminate\Support\Facades\Bus;

test('comando despacha job apenas para trends ativas de watchlists ativas', function () {
    Bus::fake();

    $activeWatchlist = Watchlist::factory()->create(['status' => Watchlist::STATUS_ACTIVE]);
    $inactiveWatchlist = Watchlist::factory()->create(['status' => Watchlist::STATUS_INACTIVE]);

    $activeTrend = Trend::factory()->for($activeWatchlist)->create([
        'application_id' => $activeWatchlist->application_id,
        'status' => Trend::STATUS_ACTIVE,
    ]);
    Trend::factory()->for($activeWatchlist)->create([
        'application_id' => $activeWatchlist->application_id,
        'status' => Trend::STATUS_INACTIVE,
    ]);
    Trend::factory()->for($inactiveWatchlist)->create([
        'application_id' => $inactiveWatchlist->application_id,
        'status' => Trend::STATUS_ACTIVE,
    ]);

    $this->artisan('trends:collect')->assertSuccessful();

    Bus::assertDispatched(CollectTrendSignalsJob::class, fn ($job) => $job->trendId === $activeTrend->id);
    Bus::assertDispatchedTimes(CollectTrendSignalsJob::class, 1);
});

test('job de coleta chama a action para a trend informada', function () {
    $trend = Trend::factory()->create();

    (new CollectTrendSignalsJob($trend->id))->handle(app(CollectTrendSignalsAction::class));

    expect($trend->fresh()->last_collected_at)->not->toBeNull();
});

test('job de coleta não falha quando a trend não existe mais', function () {
    (new CollectTrendSignalsJob(999999))->handle(app(CollectTrendSignalsAction::class));
})->throwsNoExceptions();
