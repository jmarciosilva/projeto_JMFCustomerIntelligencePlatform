<?php

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use JmfSystem\CustomerIntelligence\Services\JmfCiApiClient;

/**
 * Testes de integração do Plugin.
 *
 * Estes testes focam em validar que os componentes do plugin
 * funcionam corretamente com a JmfCiApiClient.
 */
test('jmf ci api client existe e funciona', function () {
    $apiClient = app(JmfCiApiClient::class);

    expect($apiClient)->toBeInstanceOf(JmfCiApiClient::class);
});

test('api client healthcheck funciona', function () {
    Http::fake(['*' => Http::response(['status' => 'ok'], 200)]);

    $apiClient = app(JmfCiApiClient::class);
    $result = $apiClient->healthCheck();

    expect($result)->toBeTrue();
});

test('api client getMetrics retorna array estruturado', function () {
    Http::fake(['*' => Http::response([
        'events' => 100,
        'visitors' => 50,
        'sessions' => 75,
        'conversions' => 10,
    ], 200)]);

    $apiClient = app(JmfCiApiClient::class);
    $metrics = $apiClient->getMetrics();

    expect($metrics)->toHaveKeys(['events', 'visitors', 'sessions', 'conversions']);
});

test('api client getContacts retorna paginação', function () {
    Http::fake(['*' => Http::response([
        'data' => [
            ['id' => 1, 'email' => 'user@example.com', 'name' => 'User'],
        ],
        'total' => 1,
        'per_page' => 25,
        'current_page' => 1,
    ], 200)]);

    $apiClient = app(JmfCiApiClient::class);
    $contacts = $apiClient->getContacts();

    expect($contacts)->toHaveKeys(['data', 'total', 'per_page', 'current_page']);
    expect($contacts['data'])->toHaveCount(1);
});

test('api client getContact retorna contato individual', function () {
    Http::fake(['*' => Http::response([
        'id' => 1,
        'email' => 'user@example.com',
        'name' => 'User',
        'lead_score' => 75,
    ], 200)]);

    $apiClient = app(JmfCiApiClient::class);
    $contact = $apiClient->getContact(1);

    expect($contact)->toHaveKeys(['id', 'email', 'name', 'lead_score']);
});

test('api client getContactEvents retorna eventos', function () {
    Http::fake(['*' => Http::response([
        'data' => [
            ['event_name' => 'product.viewed', 'occurred_at' => '2026-08-07T10:00:00'],
        ],
        'total' => 1,
        'per_page' => 50,
        'current_page' => 1,
    ], 200)]);

    $apiClient = app(JmfCiApiClient::class);
    $events = $apiClient->getContactEvents(1);

    expect($events)->toHaveKeys(['data', 'total', 'per_page', 'current_page']);
});

test('api client getEvents retorna eventos paginados', function () {
    Http::fake(['*' => Http::response([
        'data' => [
            ['event_name' => 'product.viewed', 'visitor_id' => 'vis-123'],
        ],
        'total' => 1,
        'per_page' => 50,
        'current_page' => 1,
    ], 200)]);

    $apiClient = app(JmfCiApiClient::class);
    $events = $apiClient->getEvents();

    expect($events)->toHaveKeys(['data', 'total', 'per_page', 'current_page']);
});

test('api client trata erros gracefully', function () {
    Http::fake(function () {
        throw new ConnectionException('Connection refused');
    });

    $apiClient = app(JmfCiApiClient::class);
    $metrics = $apiClient->getMetrics();

    // Não lança exceção, retorna dados padrão
    expect($metrics)->toEqual([
        'events' => 0,
        'visitors' => 0,
        'sessions' => 0,
        'conversions' => 0,
    ]);
});

test('api client aceita filtros em getContacts', function () {
    Http::fake(['*' => Http::response([
        'data' => [],
        'total' => 0,
        'per_page' => 25,
        'current_page' => 1,
    ], 200)]);

    $apiClient = app(JmfCiApiClient::class);
    $contacts = $apiClient->getContacts([
        'search' => 'test@example.com',
        'page' => 2,
    ]);

    expect($contacts['current_page'])->toBe(1); // Retorna padrão
});

test('api client aceita filtros em getEvents', function () {
    Http::fake(['*' => Http::response([
        'data' => [],
        'total' => 0,
        'per_page' => 50,
        'current_page' => 1,
    ], 200)]);

    $apiClient = app(JmfCiApiClient::class);
    $events = $apiClient->getEvents([
        'event_name' => 'product.viewed',
        'start_date' => '2026-08-01',
    ]);

    expect($events)->toHaveKeys(['data', 'total', 'per_page', 'current_page']);
});
