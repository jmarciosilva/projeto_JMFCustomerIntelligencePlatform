<?php

use App\Application\Analytics\Actions\GetConversionsAction;
use App\Domain\Analytics\DateRange;
use App\Models\Application;
use App\Models\Event;

test('retorna null quando não há conversion_event_name configurado', function () {
    $application = Application::factory()->create(['conversion_event_name' => null]);

    $result = app(GetConversionsAction::class)->handle($application, DateRange::today());

    expect($result)->toBeNull();
});

test('calcula total e taxa de conversão quando configurado', function () {
    $application = Application::factory()->create(['conversion_event_name' => 'contact.form_submitted']);

    Event::factory()->for($application)->create(['visitor_id' => 'v1', 'event_name' => 'page.viewed', 'occurred_at' => now()]);
    Event::factory()->for($application)->create(['visitor_id' => 'v2', 'event_name' => 'page.viewed', 'occurred_at' => now()]);
    Event::factory()->for($application)->create(['visitor_id' => 'v3', 'event_name' => 'page.viewed', 'occurred_at' => now()]);
    Event::factory()->for($application)->create(['visitor_id' => 'v4', 'event_name' => 'contact.form_submitted', 'occurred_at' => now()]);

    $result = app(GetConversionsAction::class)->handle($application, DateRange::today());

    expect($result)->toBe(['conversions' => 1, 'visitors' => 4, 'rate' => 25.0]);
});
