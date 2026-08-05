<?php

namespace JmfSystem\CustomerIntelligence\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Client\RequestException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Envia um payload (evento ou identify) para a API da JMF Customer
 * Intelligence de forma assíncrona. Nunca deixa uma falha propagar para o
 * código da aplicação cliente — apenas loga e, quando fizer sentido, deixa
 * a fila tentar de novo.
 */
class SendPayloadJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public int $tries;

    /**
     * @param  'events'|'contacts/identify'  $endpoint
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        public readonly string $endpoint,
        public readonly array $payload,
    ) {
        $this->tries = (int) config('customer-intelligence.tries', 3);
    }

    /**
     * @return list<int>
     */
    public function backoff(): array
    {
        return [5, 30, 120];
    }

    public function handle(): void
    {
        $response = Http::baseUrl(rtrim((string) config('customer-intelligence.base_url'), '/'))
            ->withToken((string) config('customer-intelligence.token'))
            ->timeout((int) config('customer-intelligence.timeout', 5))
            ->acceptJson()
            ->post($this->endpoint, $this->payload);

        if ($response->successful()) {
            return;
        }

        // Erros do cliente (ex.: 422 de validação, 401 de token inválido) nunca
        // terão sucesso numa nova tentativa — loga e desiste. Erros de servidor
        // ou de rate limit (429) são transitórios: relança para acionar o
        // retry/backoff da fila.
        if (! $response->serverError() && $response->status() !== 429) {
            Log::warning('JMF Customer Intelligence: payload rejeitado pela API.', [
                'endpoint' => $this->endpoint,
                'status' => $response->status(),
                'body' => $response->json() ?? $response->body(),
            ]);

            return;
        }

        throw new RequestException($response);
    }

    public function failed(Throwable $exception): void
    {
        Log::error('JMF Customer Intelligence: falha ao enviar payload após todas as tentativas.', [
            'endpoint' => $this->endpoint,
            'event_id' => $this->payload['event_id'] ?? null,
            'exception' => $exception->getMessage(),
        ]);
    }
}
