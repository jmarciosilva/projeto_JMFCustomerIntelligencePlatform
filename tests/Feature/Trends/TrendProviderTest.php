<?php

use App\Domain\Trends\Contracts\TrendProviderInterface;
use App\Domain\Trends\Exceptions\ProviderNotConfiguredException;
use App\Domain\Trends\GoogleTrendsProvider;
use App\Domain\Trends\InstagramTrendProvider;
use App\Domain\Trends\InternalBehaviorProvider;
use App\Domain\Trends\ManualTrendProvider;
use App\Domain\Trends\YouTubeTrendProvider;
use App\Models\Application;
use App\Models\Event;
use App\Models\Trend;

test('manual provider está sempre configurado e não coleta automaticamente', function () {
    $provider = new ManualTrendProvider;
    $trend = Trend::factory()->create();

    expect($provider->key())->toBe('manual');
    expect($provider->isConfigured())->toBeTrue();
    expect($provider->collect($trend))->toBeNull();
});

test('stubs de instagram, google trends e youtube não estão configurados e lançam exceção', function (string $class, string $key) {
    $provider = new $class;
    $trend = Trend::factory()->create();

    expect($provider->key())->toBe($key);
    expect($provider->isConfigured())->toBeFalse();

    $provider->collect($trend);
})->with([
    [InstagramTrendProvider::class, 'instagram'],
    [GoogleTrendsProvider::class, 'google_trends'],
    [YouTubeTrendProvider::class, 'youtube'],
])->throws(ProviderNotConfiguredException::class);

test('TrendProviderInterface resolve para ManualTrendProvider por padrão', function () {
    $provider = app(TrendProviderInterface::class);

    expect($provider)->toBeInstanceOf(ManualTrendProvider::class);
});

test('internal behavior provider conta eventos product.search e product.viewed que casam com o termo', function () {
    $application = Application::factory()->create();
    $trend = Trend::factory()->for($application)->create(['term' => 'cafeteira']);

    Event::factory()->for($application)->count(3)->create([
        'event_name' => 'product.search',
        'properties' => ['search_term' => 'cafeteira espresso', 'results_count' => 10],
        'occurred_at' => now()->subDays(1),
    ]);
    Event::factory()->for($application)->count(2)->create([
        'event_name' => 'product.viewed',
        'properties' => ['category' => 'cafeteira', 'product_id' => 1],
        'occurred_at' => now()->subDays(2),
    ]);
    Event::factory()->for($application)->create([
        'event_name' => 'product.search',
        'properties' => ['search_term' => 'panela de pressão', 'results_count' => 4],
        'occurred_at' => now()->subDays(1),
    ]);

    $provider = new InternalBehaviorProvider;
    $data = $provider->collect($trend);

    expect($data)->not->toBeNull();
    expect($data['mentions'])->toBe(5);
    expect($data['metadata']['previous_mentions'])->toBe(0);
    expect($data['velocity'])->toBe(100.0);
});

test('internal behavior provider retorna null para termo vazio', function () {
    $trend = Trend::factory()->create(['term' => '   ']);

    $provider = new InternalBehaviorProvider;

    expect($provider->collect($trend))->toBeNull();
});

test('internal behavior provider isola contagem por application', function () {
    $applicationA = Application::factory()->create();
    $applicationB = Application::factory()->create();
    $trend = Trend::factory()->for($applicationA)->create(['term' => 'aspirador']);

    Event::factory()->for($applicationA)->create([
        'event_name' => 'product.search',
        'properties' => ['search_term' => 'aspirador de pó'],
        'occurred_at' => now()->subDay(),
    ]);
    Event::factory()->for($applicationB)->create([
        'event_name' => 'product.search',
        'properties' => ['search_term' => 'aspirador de pó'],
        'occurred_at' => now()->subDay(),
    ]);

    $provider = new InternalBehaviorProvider;
    $data = $provider->collect($trend);

    expect($data['mentions'])->toBe(1);
});
