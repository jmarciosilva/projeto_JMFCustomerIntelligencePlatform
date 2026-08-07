<?php

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use JmfSystem\CustomerIntelligence\Jobs\SendPayloadJob;

test('envia o payload para a URL, headers e endpoint corretos', function () {
    Http::fake(['*' => Http::response(['status' => 'accepted'], 202)]);

    $job = new SendPayloadJob('events', [
        'event_id' => 'evt-123',
        'event_name' => 'page.viewed',
        'visitor_id' => 'vis-123',
        'occurred_at' => now()->toIso8601String(),
    ]);
    $job->handle();

    Http::assertSent(function ($request) {
        return $request->url() === 'https://ci.test/api/v1/events'
            && $request->hasHeader('Authorization', 'Bearer test-token')
            && $request['event_name'] === 'page.viewed';
    });
});

test('resposta 500 aciona retry (relança exceção)', function () {
    Http::fake(['*' => Http::response(['message' => 'erro'], 500)]);

    $job = new SendPayloadJob('events', [
        'event_id' => 'evt-123',
        'event_name' => 'page.viewed',
        'visitor_id' => 'vis-123',
        'occurred_at' => now()->toIso8601String(),
    ]);

    expect(fn () => $job->handle())->toThrow(RequestException::class);
});

test('resposta 422 não relança exceção (sem retry) e loga warning', function () {
    Http::fake(['*' => Http::response(['message' => 'invalido'], 422)]);
    Log::spy();

    $job = new SendPayloadJob('events', [
        'event_id' => 'evt-123',
        'event_name' => 'page.viewed',
        'visitor_id' => 'vis-123',
        'occurred_at' => now()->toIso8601String(),
    ]);
    $job->handle();

    Log::shouldHaveReceived('warning')->once();
});

test('failed() loga erro com contexto do payload', function () {
    Log::spy();

    $job = new SendPayloadJob('events', [
        'event_id' => 'evt-123',
        'event_name' => 'page.viewed',
        'visitor_id' => 'vis-123',
        'occurred_at' => now()->toIso8601String(),
    ]);
    $job->failed(new Exception('falha de rede'));

    Log::shouldHaveReceived('error')->once();
});
