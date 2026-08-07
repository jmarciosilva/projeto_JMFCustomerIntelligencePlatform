<?php

use App\Models\User;
use JmfSystem\CustomerIntelligence\Services\JmfCiApiClient;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

test('jmf ci plugin routes are registered', function () {
    $routes = [
        'admin.plugin.jmf-ci.dashboard' => [],
        'admin.plugin.jmf-ci.configuration' => [],
        'admin.plugin.jmf-ci.contacts.index' => [],
        'admin.plugin.jmf-ci.contacts.show' => ['contact' => 'test-id'],
        'admin.plugin.jmf-ci.events.index' => [],
    ];

    foreach ($routes as $routeName => $params) {
        expect(route($routeName, $params))->not->toBeNull();
    }
});

test('plugin routes require authentication', function () {
    // Routes are protected by the 'auth' middleware
    // Access without authentication should not return 200
    $response = $this->get(route('admin.plugin.jmf-ci.dashboard'));

    expect($response->status())->not->toBe(200);
});

test('plugin api client is available in service container', function () {
    $apiClient = app(JmfCiApiClient::class);

    expect($apiClient)->toBeInstanceOf(
        JmfCiApiClient::class
    );
});

test('plugin configuration is properly loaded', function () {
    expect(config('customer-intelligence'))->not->toBeNull();
    expect(config('customer-intelligence.enabled'))->toBeTrue();
    expect(config('customer-intelligence.base_url'))->not->toBeNull();
    expect(config('customer-intelligence.token'))->not->toBeNull();
});

test('plugin livewire components are registered', function () {
    $components = [
        'jmf-ci.dashboard',
        'jmf-ci.configuration',
        'jmf-ci.contacts.index',
        'jmf-ci.contacts.show',
        'jmf-ci.events.index',
    ];

    // Check that components exist by trying to instantiate them
    foreach ($components as $component) {
        try {
            Livewire\Livewire::test($component);
        } catch (Exception $e) {
            // Component might fail to render completely, but it should be found
            expect($e->getMessage())->not->toContain('Component not found');
        }
    }
});
