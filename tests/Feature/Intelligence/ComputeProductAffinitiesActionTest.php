<?php

use App\Application\Intelligence\Actions\ComputeProductAffinitiesAction;
use App\Models\Application;
use App\Models\Event;
use App\Models\ProductAffinity;

test('conta pares co-ocorrentes corretamente', function () {
    $application = Application::factory()->create();

    foreach (['visitor_1', 'visitor_2'] as $visitorId) {
        Event::factory()->for($application)->create([
            'visitor_id' => $visitorId,
            'subject_type' => 'Product',
            'subject_id' => '1',
        ]);

        Event::factory()->for($application)->create([
            'visitor_id' => $visitorId,
            'subject_type' => 'Product',
            'subject_id' => '2',
        ]);
    }

    app(ComputeProductAffinitiesAction::class)->handle($application);

    $affinity = ProductAffinity::query()
        ->where('application_id', $application->id)
        ->where('subject_type', 'Product')
        ->first();

    expect($affinity)->not->toBeNull()
        ->and([$affinity->subject_id_a, $affinity->subject_id_b])->toBe(['1', '2'])
        ->and($affinity->co_occurrences)->toBe(2);
});

test('afinidade é isolada por aplicação', function () {
    $applicationA = Application::factory()->create();
    $applicationB = Application::factory()->create();

    foreach ([$applicationA, $applicationB] as $application) {
        Event::factory()->for($application)->create(['visitor_id' => 'v1', 'subject_type' => 'Product', 'subject_id' => '1']);
        Event::factory()->for($application)->create(['visitor_id' => 'v1', 'subject_type' => 'Product', 'subject_id' => '2']);
    }

    app(ComputeProductAffinitiesAction::class)->handle($applicationA);

    expect(ProductAffinity::query()->where('application_id', $applicationA->id)->count())->toBe(1)
        ->and(ProductAffinity::query()->where('application_id', $applicationB->id)->count())->toBe(0);
});

test('visitante com apenas um subject não gera par', function () {
    $application = Application::factory()->create();

    Event::factory()->for($application)->create(['visitor_id' => 'v1', 'subject_type' => 'Product', 'subject_id' => '1']);

    app(ComputeProductAffinitiesAction::class)->handle($application);

    expect(ProductAffinity::query()->where('application_id', $application->id)->count())->toBe(0);
});
