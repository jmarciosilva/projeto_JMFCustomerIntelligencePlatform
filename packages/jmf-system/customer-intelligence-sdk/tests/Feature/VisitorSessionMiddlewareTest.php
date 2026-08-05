<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use JmfSystem\CustomerIntelligence\Support\VisitorContext;

function registerVisitorTestRoute(): void
{
    Route::middleware('web')->get('/test-visitor', function () {
        $context = app(VisitorContext::class);

        return response()->json([
            'visitor_id' => $context->visitorId(),
            'session_id' => $context->sessionId(),
        ]);
    });
}

test('primeira requisição gera cookies de visitor e session', function () {
    registerVisitorTestRoute();

    $response = $this->get('/test-visitor');

    $response->assertOk();
    $response->assertCookie('jmf_ci_visitor_id');
    $response->assertCookie('jmf_ci_session_id');
    expect($response->json('visitor_id'))->not->toBeNull();
    expect($response->json('session_id'))->not->toBeNull();
});

test('requisição seguinte com cookies existentes reaproveita os mesmos IDs', function () {
    registerVisitorTestRoute();

    $existingVisitorId = (string) Str::ulid();
    $existingSessionId = (string) Str::ulid();

    $response = $this->withCookie('jmf_ci_visitor_id', $existingVisitorId)
        ->withCookie('jmf_ci_session_id', $existingSessionId)
        ->get('/test-visitor');

    $response->assertOk();
    expect($response->json('visitor_id'))->toBe($existingVisitorId);
    expect($response->json('session_id'))->toBe($existingSessionId);
});

test('sessão ausente gera novo session_id mantendo o mesmo visitor_id', function () {
    registerVisitorTestRoute();

    $existingVisitorId = (string) Str::ulid();

    $response = $this->withCookie('jmf_ci_visitor_id', $existingVisitorId)
        ->get('/test-visitor');

    $response->assertOk();
    expect($response->json('visitor_id'))->toBe($existingVisitorId);
    expect($response->json('session_id'))->not->toBeNull();
});
