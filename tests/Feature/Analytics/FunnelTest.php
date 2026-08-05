<?php

use App\Application\Analytics\Actions\GetFunnelAction;
use App\Domain\Analytics\DateRange;
use App\Models\Application;
use App\Models\Event;

test('funil estrito conta apenas visitantes que passaram pela etapa anterior', function () {
    $application = Application::factory()->create();

    // visitor_1 completa as duas etapas.
    Event::factory()->for($application)->create(['visitor_id' => 'visitor_1', 'event_name' => 'session.started', 'occurred_at' => now()]);
    Event::factory()->for($application)->create(['visitor_id' => 'visitor_1', 'event_name' => 'article.viewed', 'occurred_at' => now()]);

    // visitor_2 só dispara a segunda etapa (pulou a primeira) — não deve contar nela.
    Event::factory()->for($application)->create(['visitor_id' => 'visitor_2', 'event_name' => 'article.viewed', 'occurred_at' => now()]);

    // visitor_3 só passa pela primeira etapa.
    Event::factory()->for($application)->create(['visitor_id' => 'visitor_3', 'event_name' => 'session.started', 'occurred_at' => now()]);

    $funnel = app(GetFunnelAction::class)->handle($application, DateRange::today(), ['session.started', 'article.viewed']);

    expect($funnel[0]['visitors'])->toBe(2)
        ->and($funnel[0]['conversion_rate'])->toBe(100.0)
        ->and($funnel[1]['visitors'])->toBe(1)
        ->and($funnel[1]['conversion_rate'])->toBe(50.0);
});
