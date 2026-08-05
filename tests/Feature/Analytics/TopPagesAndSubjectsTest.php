<?php

use App\Application\Analytics\Actions\GetTopPagesAction;
use App\Application\Analytics\Actions\GetTopSubjectsAction;
use App\Domain\Analytics\DateRange;
use App\Models\Application;
use App\Models\Event;

test('top páginas agrega corretamente por page_url', function () {
    $application = Application::factory()->create();

    Event::factory()->for($application)->count(3)->create([
        'event_name' => 'page.viewed',
        'context' => ['page_url' => '/blog/a'],
        'occurred_at' => now(),
    ]);

    Event::factory()->for($application)->create([
        'event_name' => 'page.viewed',
        'context' => ['page_url' => '/blog/b'],
        'occurred_at' => now(),
    ]);

    $topPages = app(GetTopPagesAction::class)->handle($application, DateRange::today());

    expect($topPages[0])->toMatchArray(['page_url' => '/blog/a', 'total' => 3])
        ->and($topPages[1])->toMatchArray(['page_url' => '/blog/b', 'total' => 1]);
});

test('top subjects respeita event_name e subject_type', function () {
    $application = Application::factory()->create();

    Event::factory()->for($application)->count(2)->create([
        'event_name' => 'article.viewed',
        'subject_type' => 'Article',
        'subject_id' => '15',
        'occurred_at' => now(),
    ]);

    Event::factory()->for($application)->create([
        'event_name' => 'service.viewed',
        'subject_type' => 'Service',
        'subject_id' => '99',
        'occurred_at' => now(),
    ]);

    $topArticles = app(GetTopSubjectsAction::class)->handle($application, DateRange::today(), 'article.viewed', 'Article');

    expect($topArticles)->toHaveCount(1)
        ->and($topArticles[0])->toMatchArray(['subject_id' => '15', 'subject_type' => 'Article', 'total' => 2]);
});
