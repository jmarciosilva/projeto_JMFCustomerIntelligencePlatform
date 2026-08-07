<?php

namespace JmfSystem\CustomerIntelligence\Services;

use Illuminate\Support\Facades\Http;
use JmfSystem\CustomerIntelligence\Client;

/**
 * Cliente helper para integração com UI components.
 *
 * Fornece métodos de alto nível para recuperar dados da API de Customer Intelligence,
 * com tratamento de erros gracioso (nunca lança exceção). Usado pelos componentes
 * Livewire para popular dashboards, tabelas, etc.
 */
class JmfCiApiClient
{
    public function __construct(private readonly Client $client) {}

    /**
     * Valida a conexão com a API.
     *
     * @return bool True se API está online, false caso contrário
     */
    public function healthCheck(): bool
    {
        return $this->client->healthCheck();
    }

    /**
     * Recupera métricas agregadas da plataforma.
     *
     * @param  array{start_date?: string, end_date?: string}  $filters
     * @return array{events: int, visitors: int, sessions: int, conversions: int, trend?: array}
     */
    public function getMetrics(array $filters = []): array
    {
        try {
            $response = Http::baseUrl(rtrim((string) config('customer-intelligence.base_url'), '/'))
                ->withToken((string) config('customer-intelligence.token'))
                ->timeout((int) config('customer-intelligence.timeout', 5))
                ->get('api/v1/metrics', $filters);

            if ($response->successful()) {
                return $response->json();
            }

            return [
                'events' => 0,
                'visitors' => 0,
                'sessions' => 0,
                'conversions' => 0,
            ];
        } catch (\Throwable) {
            return [
                'events' => 0,
                'visitors' => 0,
                'sessions' => 0,
                'conversions' => 0,
            ];
        }
    }

    /**
     * Recupera lista paginada de contatos.
     *
     * @param  array{page?: int, per_page?: int, search?: string, start_date?: string, end_date?: string}  $filters
     * @return array{data: array, total: int, per_page: int, current_page: int}
     */
    public function getContacts(array $filters = []): array
    {
        try {
            $page = $filters['page'] ?? 1;
            $perPage = $filters['per_page'] ?? 25;

            $response = Http::baseUrl(rtrim((string) config('customer-intelligence.base_url'), '/'))
                ->withToken((string) config('customer-intelligence.token'))
                ->timeout((int) config('customer-intelligence.timeout', 5))
                ->get('api/v1/contacts', [
                    'page' => $page,
                    'per_page' => $perPage,
                    'search' => $filters['search'] ?? null,
                    'start_date' => $filters['start_date'] ?? null,
                    'end_date' => $filters['end_date'] ?? null,
                ]);

            if ($response->successful()) {
                return $response->json();
            }

            return ['data' => [], 'total' => 0, 'per_page' => $perPage, 'current_page' => $page];
        } catch (\Throwable) {
            return ['data' => [], 'total' => 0, 'per_page' => 25, 'current_page' => 1];
        }
    }

    /**
     * Recupera detalhes de um contato específico.
     *
     * @param  int|string  $contactId
     */
    public function getContact($contactId): ?array
    {
        try {
            $response = Http::baseUrl(rtrim((string) config('customer-intelligence.base_url'), '/'))
                ->withToken((string) config('customer-intelligence.token'))
                ->timeout((int) config('customer-intelligence.timeout', 5))
                ->get("api/v1/contacts/{$contactId}");

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Recupera eventos de um contato.
     *
     * @param  int|string  $contactId
     * @param  array{page?: int, per_page?: int}  $filters
     * @return array{data: array, total: int, per_page: int, current_page: int}
     */
    public function getContactEvents($contactId, array $filters = []): array
    {
        try {
            $page = $filters['page'] ?? 1;
            $perPage = $filters['per_page'] ?? 50;

            $response = Http::baseUrl(rtrim((string) config('customer-intelligence.base_url'), '/'))
                ->withToken((string) config('customer-intelligence.token'))
                ->timeout((int) config('customer-intelligence.timeout', 5))
                ->get("api/v1/contacts/{$contactId}/events", [
                    'page' => $page,
                    'per_page' => $perPage,
                ]);

            if ($response->successful()) {
                return $response->json();
            }

            return ['data' => [], 'total' => 0, 'per_page' => $perPage, 'current_page' => $page];
        } catch (\Throwable) {
            return ['data' => [], 'total' => 0, 'per_page' => 50, 'current_page' => 1];
        }
    }

    /**
     * Recupera lista paginada de eventos.
     *
     * @param  array{page?: int, per_page?: int, event_name?: string, search?: string, start_date?: string, end_date?: string}  $filters
     * @return array{data: array, total: int, per_page: int, current_page: int}
     */
    public function getEvents(array $filters = []): array
    {
        try {
            $page = $filters['page'] ?? 1;
            $perPage = $filters['per_page'] ?? 50;

            $response = Http::baseUrl(rtrim((string) config('customer-intelligence.base_url'), '/'))
                ->withToken((string) config('customer-intelligence.token'))
                ->timeout((int) config('customer-intelligence.timeout', 5))
                ->get('api/v1/events', [
                    'page' => $page,
                    'per_page' => $perPage,
                    'event_name' => $filters['event_name'] ?? null,
                    'search' => $filters['search'] ?? null,
                    'start_date' => $filters['start_date'] ?? null,
                    'end_date' => $filters['end_date'] ?? null,
                ]);

            if ($response->successful()) {
                return $response->json();
            }

            return ['data' => [], 'total' => 0, 'per_page' => $perPage, 'current_page' => $page];
        } catch (\Throwable) {
            return ['data' => [], 'total' => 0, 'per_page' => 50, 'current_page' => 1];
        }
    }
}
