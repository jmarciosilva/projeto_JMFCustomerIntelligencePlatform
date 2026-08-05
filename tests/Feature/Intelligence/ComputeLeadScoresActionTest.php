<?php

use App\Application\Intelligence\Actions\ComputeLeadScoresAction;
use App\Models\Application;
use App\Models\Contact;
use App\Models\Event;
use App\Models\Tenant;
use App\Models\Visitor;

test('soma pontos de eventos de múltiplos visitors/aplicações do mesmo contato', function () {
    $tenant = Tenant::factory()->create();
    $applicationA = Application::factory()->for($tenant)->create();
    $applicationB = Application::factory()->for($tenant)->create();

    $contact = Contact::factory()->for($tenant)->create();

    $visitorA = Visitor::factory()->for($applicationA)->create(['visitor_id' => 'visitor_a', 'contact_id' => $contact->id]);
    $visitorB = Visitor::factory()->for($applicationB)->create(['visitor_id' => 'visitor_b', 'contact_id' => $contact->id]);

    Event::factory()->for($applicationA)->create([
        'visitor_id' => $visitorA->visitor_id,
        'event_name' => 'contact.form_submitted', // 20 pontos
    ]);

    Event::factory()->for($applicationB)->count(2)->create([
        'visitor_id' => $visitorB->visitor_id,
        'event_name' => 'article.viewed', // 2 pontos cada
    ]);

    app(ComputeLeadScoresAction::class)->handle();

    $contact->refresh();

    expect($contact->lead_score)->toBe(24)
        ->and($contact->lead_score_computed_at)->not->toBeNull();
});

test('contato sem eventos fica com lead_score zero', function () {
    $contact = Contact::factory()->create();

    app(ComputeLeadScoresAction::class)->handle();

    $contact->refresh();

    expect($contact->lead_score)->toBe(0)
        ->and($contact->lead_score_computed_at)->not->toBeNull();
});

test('evento fora do mapa de pontuação não soma nada', function () {
    $application = Application::factory()->create();
    $contact = Contact::factory()->for($application->tenant)->create();
    $visitor = Visitor::factory()->for($application)->create(['visitor_id' => 'visitor_x', 'contact_id' => $contact->id]);

    Event::factory()->for($application)->create([
        'visitor_id' => $visitor->visitor_id,
        'event_name' => 'evento.desconhecido',
    ]);

    app(ComputeLeadScoresAction::class)->handle();

    expect($contact->fresh()->lead_score)->toBe(0);
});
