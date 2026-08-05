<?php

use App\Application\Intelligence\Actions\GetRecommendationsAction;
use App\Models\Application;
use App\Models\Event;
use App\Models\ProductAffinity;

test('prioriza recomendações por afinidade, ordenadas por co_occurrences', function () {
    $application = Application::factory()->create();

    ProductAffinity::factory()->for($application)->create([
        'subject_type' => 'Product', 'subject_id_a' => '1', 'subject_id_b' => '2', 'co_occurrences' => 5,
    ]);
    ProductAffinity::factory()->for($application)->create([
        'subject_type' => 'Product', 'subject_id_a' => '1', 'subject_id_b' => '3', 'co_occurrences' => 3,
    ]);

    $recommendations = app(GetRecommendationsAction::class)->handle($application, 'Product', '1', 2);

    expect($recommendations)->toBe([
        ['subject_id' => '2', 'subject_type' => 'Product', 'score' => 5, 'source' => 'affinity'],
        ['subject_id' => '3', 'subject_type' => 'Product', 'score' => 3, 'source' => 'affinity'],
    ]);
});

test('completa com popularidade quando não há afinidade suficiente', function () {
    $application = Application::factory()->create();

    ProductAffinity::factory()->for($application)->create([
        'subject_type' => 'Product', 'subject_id_a' => '1', 'subject_id_b' => '2', 'co_occurrences' => 5,
    ]);

    Event::factory()->for($application)->count(3)->create(['subject_type' => 'Product', 'subject_id' => '4']);
    Event::factory()->for($application)->count(2)->create(['subject_type' => 'Product', 'subject_id' => '6']);
    Event::factory()->for($application)->create(['subject_type' => 'Product', 'subject_id' => '5']);

    $recommendations = app(GetRecommendationsAction::class)->handle($application, 'Product', '1', 3);

    expect($recommendations)->toBe([
        ['subject_id' => '2', 'subject_type' => 'Product', 'score' => 5, 'source' => 'affinity'],
        ['subject_id' => '4', 'subject_type' => 'Product', 'score' => 3, 'source' => 'popularity'],
        ['subject_id' => '6', 'subject_type' => 'Product', 'score' => 2, 'source' => 'popularity'],
    ]);
});

test('nunca recomenda o próprio subject consultado', function () {
    $application = Application::factory()->create();

    Event::factory()->for($application)->count(5)->create(['subject_type' => 'Product', 'subject_id' => '1']);
    Event::factory()->for($application)->count(1)->create(['subject_type' => 'Product', 'subject_id' => '2']);

    $recommendations = app(GetRecommendationsAction::class)->handle($application, 'Product', '1', 5);

    expect(collect($recommendations)->pluck('subject_id'))->not->toContain('1');
});
