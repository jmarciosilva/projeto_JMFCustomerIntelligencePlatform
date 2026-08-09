<?php

use App\Domain\Trends\WatchlistTrendSynchronizer;
use App\Models\Trend;
use App\Models\TrendSnapshot;
use App\Models\Watchlist;

test('sync cria uma trend para cada keyword e hashtag', function () {
    $watchlist = Watchlist::factory()->create([
        'keywords' => ['cafeteira', 'air fryer'],
        'hashtags' => ['#cantinhodocafe'],
    ]);

    (new WatchlistTrendSynchronizer)->sync($watchlist);

    expect(Trend::where('watchlist_id', $watchlist->id)->count())->toBe(3);
    $this->assertDatabaseHas('trends', [
        'watchlist_id' => $watchlist->id,
        'term' => 'cafeteira',
        'type' => Trend::TYPE_KEYWORD,
        'status' => Trend::STATUS_ACTIVE,
    ]);
    $this->assertDatabaseHas('trends', [
        'watchlist_id' => $watchlist->id,
        'term' => '#cantinhodocafe',
        'type' => Trend::TYPE_HASHTAG,
        'status' => Trend::STATUS_ACTIVE,
    ]);
});

test('sync é idempotente e não duplica trends já existentes', function () {
    $watchlist = Watchlist::factory()->create(['keywords' => ['cafeteira'], 'hashtags' => []]);

    $synchronizer = new WatchlistTrendSynchronizer;
    $synchronizer->sync($watchlist);
    $synchronizer->sync($watchlist);

    expect(Trend::where('watchlist_id', $watchlist->id)->count())->toBe(1);
});

test('remover um termo da watchlist marca a trend correspondente como inativa sem apagar histórico', function () {
    $watchlist = Watchlist::factory()->create(['keywords' => ['cafeteira', 'aspirador'], 'hashtags' => []]);
    (new WatchlistTrendSynchronizer)->sync($watchlist);

    $trend = Trend::where('watchlist_id', $watchlist->id)->where('term', 'aspirador')->first();
    TrendSnapshot::factory()->for($trend)->create();

    $watchlist->update(['keywords' => ['cafeteira']]);
    (new WatchlistTrendSynchronizer)->sync($watchlist);

    $trend->refresh();
    expect($trend->status)->toBe(Trend::STATUS_INACTIVE);
    expect($trend->snapshots()->count())->toBe(1);

    $cafeteira = Trend::where('watchlist_id', $watchlist->id)->where('term', 'cafeteira')->first();
    expect($cafeteira->status)->toBe(Trend::STATUS_ACTIVE);
});

test('sync sem nenhum termo desativa todas as trends da watchlist', function () {
    $watchlist = Watchlist::factory()->create(['keywords' => ['cafeteira'], 'hashtags' => []]);
    (new WatchlistTrendSynchronizer)->sync($watchlist);

    $watchlist->update(['keywords' => [], 'hashtags' => []]);
    (new WatchlistTrendSynchronizer)->sync($watchlist);

    expect(Trend::where('watchlist_id', $watchlist->id)->where('status', Trend::STATUS_ACTIVE)->count())->toBe(0);
});
