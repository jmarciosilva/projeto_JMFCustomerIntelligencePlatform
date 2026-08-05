<?php

namespace JmfSystem\CustomerIntelligence;

use Illuminate\Support\Str;
use JmfSystem\CustomerIntelligence\Jobs\SendPayloadJob;
use JmfSystem\CustomerIntelligence\Support\RequestContextResolver;
use JmfSystem\CustomerIntelligence\Support\VisitorContext;

class Client
{
    public function __construct(
        private readonly VisitorContext $visitorContext,
        private readonly RequestContextResolver $contextResolver,
    ) {}

    /**
     * @param  array<string, mixed>  $traits  external_id, email, name, phone, properties.
     * @param  list<array{purpose: string, granted: bool}>  $consents
     */
    public function identify(array $traits = [], array $consents = []): void
    {
        $payload = array_filter([
            'visitor_id' => $this->visitorContext->visitorId(),
            'external_id' => $traits['external_id'] ?? null,
            'email' => $traits['email'] ?? null,
            'name' => $traits['name'] ?? null,
            'phone' => $traits['phone'] ?? null,
            'properties' => $traits['properties'] ?? null,
            'consents' => $consents !== [] ? $consents : null,
        ], fn ($value) => $value !== null);

        $this->dispatch('contacts/identify', $payload);
    }

    /**
     * @param  array<string, mixed>  $properties
     */
    public function track(
        string $eventName,
        array $properties = [],
        ?string $subjectType = null,
        string|int|null $subjectId = null,
    ): void {
        $payload = array_filter([
            'event_id' => (string) Str::ulid(),
            'event_name' => $eventName,
            'visitor_id' => $this->visitorContext->visitorId(),
            'session_id' => $this->visitorContext->sessionId(),
            'occurred_at' => now()->toIso8601String(),
            'properties' => $properties !== [] ? $properties : null,
            'context' => $this->contextResolver->resolve(),
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
        ], fn ($value) => $value !== null);

        $this->dispatch('events', $payload);
    }

    /**
     * Semanticamente idêntico a track() — existe como método próprio para
     * deixar explícito, no código da aplicação cliente, qual evento
     * representa uma conversão. O mapeamento real acontece no servidor via
     * `Application.conversion_event_name` (Fase 06); o SDK não duplica essa
     * configuração no lado cliente.
     *
     * @param  array<string, mixed>  $properties
     */
    public function conversion(string $eventName, array $properties = []): void
    {
        $this->track($eventName, $properties);
    }

    /**
     * @param  'events'|'contacts/identify'  $endpoint
     * @param  array<string, mixed>  $payload
     */
    private function dispatch(string $endpoint, array $payload): void
    {
        dispatch(new SendPayloadJob($endpoint, $payload))
            ->onConnection(config('customer-intelligence.queue_connection'))
            ->onQueue(config('customer-intelligence.queue'));
    }
}
