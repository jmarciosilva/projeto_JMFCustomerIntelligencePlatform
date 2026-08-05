<?php

use App\Models\Application;
use App\Models\DailyMetric;
use App\Models\Event;

test('gera as métricas esperadas para um dia', function () {
    $application = Application::factory()->create(['conversion_event_name' => 'contact.form_submitted']);
    $yesterday = now()->subDay();

    Event::factory()->for($application)->create([
        'visitor_id' => 'v1',
        'session_id' => 's1',
        'event_name' => 'page.viewed',
        'occurred_at' => $yesterday,
    ]);

    Event::factory()->for($application)->create([
        'visitor_id' => 'v2',
        'session_id' => 's2',
        'event_name' => 'contact.form_submitted',
        'occurred_at' => $yesterday,
    ]);

    $this->artisan('metrics:aggregate-daily', ['--date' => $yesterday->toDateString()])->assertSuccessful();

    $metrics = DailyMetric::query()
        ->where('application_id', $application->id)
        ->pluck('metric_value', 'metric_key')
        ->map(fn ($value) => (int) $value);

    expect($metrics['events_total'])->toBe(2)
        ->and($metrics['visitors_unique'])->toBe(2)
        ->and($metrics['sessions_unique'])->toBe(2)
        ->and($metrics['pageviews_total'])->toBe(1)
        ->and($metrics['conversions_total'])->toBe(1);
});

test('comando é idempotente ao rodar duas vezes', function () {
    $application = Application::factory()->create();
    $yesterday = now()->subDay();

    Event::factory()->for($application)->create(['occurred_at' => $yesterday]);

    $this->artisan('metrics:aggregate-daily', ['--date' => $yesterday->toDateString()])->assertSuccessful();
    $this->artisan('metrics:aggregate-daily', ['--date' => $yesterday->toDateString()])->assertSuccessful();

    expect(DailyMetric::query()->where('application_id', $application->id)->where('metric_key', 'events_total')->count())->toBe(1);
});

test('não quebra para aplicação sem eventos no dia', function () {
    Application::factory()->create();

    $this->artisan('metrics:aggregate-daily', ['--date' => now()->subDay()->toDateString()])->assertSuccessful();
});
