<?php

use App\Application\Analytics\Actions\GetDashboardOverviewAction;
use App\Domain\Analytics\DateRange;
use App\Models\Application;
use App\Models\Event;

test('totais do período são calculados corretamente', function () {
    $application = Application::factory()->create();

    Event::factory()->for($application)->count(3)->create([
        'visitor_id' => 'visitor_a',
        'session_id' => 'session_a',
        'occurred_at' => now()->subDay(),
    ]);

    Event::factory()->for($application)->create([
        'visitor_id' => 'visitor_b',
        'session_id' => 'session_b',
        'occurred_at' => now()->subDay(),
    ]);

    $overview = app(GetDashboardOverviewAction::class)->handle($application, DateRange::lastDays(7));

    expect($overview['totals']['events_total'])->toBe(4)
        ->and($overview['totals']['visitors_unique'])->toBe(2)
        ->and($overview['totals']['sessions_unique'])->toBe(2);
});

test('totais isolam corretamente entre aplicações', function () {
    $appA = Application::factory()->create();
    $appB = Application::factory()->create();

    Event::factory()->for($appA)->count(2)->create(['occurred_at' => now()]);
    Event::factory()->for($appB)->count(5)->create(['occurred_at' => now()]);

    $overviewA = app(GetDashboardOverviewAction::class)->handle($appA, DateRange::today());

    expect($overviewA['totals']['events_total'])->toBe(2);
});

test('trend do dia corrente é calculado ao vivo mesmo sem daily_metrics gerado', function () {
    $application = Application::factory()->create();

    Event::factory()->for($application)->count(3)->create(['occurred_at' => now()]);

    $overview = app(GetDashboardOverviewAction::class)->handle($application, DateRange::today());

    expect($overview['trend'])->toHaveCount(1)
        ->and($overview['trend'][0]['events_total'])->toBe(3);
});
