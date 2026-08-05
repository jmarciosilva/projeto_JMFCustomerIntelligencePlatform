<?php

use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Route;
use JmfSystem\CustomerIntelligence\Facades\CustomerIntelligence;
use JmfSystem\CustomerIntelligence\Jobs\SendPayloadJob;

test('track() despacha SendPayloadJob com o payload correto', function () {
    Queue::fake();

    Route::middleware('web')->get('/test-track', function () {
        CustomerIntelligence::track('article.viewed', ['article_id' => 5], 'Article', 5);

        return 'ok';
    });

    $this->get('/test-track?utm_source=linkedin')->assertOk();

    Queue::assertPushed(SendPayloadJob::class, function (SendPayloadJob $job) {
        return $job->endpoint === 'events'
            && $job->payload['event_name'] === 'article.viewed'
            && $job->payload['properties'] === ['article_id' => 5]
            && $job->payload['subject_type'] === 'Article'
            && $job->payload['subject_id'] === 5
            && $job->payload['context']['utm_source'] === 'linkedin'
            && ! empty($job->payload['visitor_id'])
            && ! empty($job->payload['session_id'])
            && ! empty($job->payload['event_id']);
    });
});

test('identify() despacha SendPayloadJob para contacts/identify', function () {
    Queue::fake();

    Route::middleware('web')->get('/test-identify', function () {
        CustomerIntelligence::identify(['email' => 'a@a.com', 'name' => 'Fulano']);

        return 'ok';
    });

    $this->get('/test-identify')->assertOk();

    Queue::assertPushed(SendPayloadJob::class, function (SendPayloadJob $job) {
        return $job->endpoint === 'contacts/identify'
            && $job->payload['email'] === 'a@a.com'
            && $job->payload['name'] === 'Fulano'
            && ! empty($job->payload['visitor_id']);
    });
});

test('conversion() delega para track() com o mesmo event_name', function () {
    Queue::fake();

    Route::middleware('web')->get('/test-conversion', function () {
        CustomerIntelligence::conversion('contact.form_submitted', ['form' => 'contato']);

        return 'ok';
    });

    $this->get('/test-conversion')->assertOk();

    Queue::assertPushed(SendPayloadJob::class, function (SendPayloadJob $job) {
        return $job->endpoint === 'events'
            && $job->payload['event_name'] === 'contact.form_submitted'
            && $job->payload['properties'] === ['form' => 'contato'];
    });
});
