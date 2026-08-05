<?php

use App\Application\Analytics\Actions\GetUtmBreakdownAction;
use App\Domain\Analytics\DateRange;
use App\Models\Application;
use App\Models\Event;

test('utm breakdown agrupa corretamente e ignora eventos sem utm', function () {
    $application = Application::factory()->create();

    Event::factory()->for($application)->count(2)->create([
        'context' => ['utm_source' => 'linkedin', 'utm_medium' => 'social', 'utm_campaign' => 'lancamento'],
        'occurred_at' => now(),
    ]);

    Event::factory()->for($application)->create([
        'context' => [],
        'occurred_at' => now(),
    ]);

    $breakdown = app(GetUtmBreakdownAction::class)->handle($application, DateRange::today());

    expect($breakdown)->toHaveCount(1)
        ->and($breakdown[0])->toMatchArray([
            'utm_source' => 'linkedin',
            'utm_medium' => 'social',
            'utm_campaign' => 'lancamento',
            'total' => 2,
        ]);
});
