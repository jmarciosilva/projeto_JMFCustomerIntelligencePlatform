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
 *
 * Retry logic:
 * - 5xx (erro de servidor): retry com exponential backoff
 * - 429 (rate limit): retry com exponential backoff
 * - 4xx (erro de cliente): sem retry, apenas log
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
     * Exponential backoff para retries: [5s, 30s, 120s].
     *
     * @return list<int>
     */
    public function backoff(): array
    {
        return config('customer-intelligence.backoff', [5, 30, 120]);
    }

    /**
     * Validação de payload antes de enviar.
     *
     * @return bool
     *
     * @throws \RuntimeException se payload inválido
     */
    private function validatePayload(): bool
    {
        if (empty($this->payload)) {
            throw new \RuntimeException('Payload vazio não pode ser enviado');
        }

        if (! isset($this->payload['event_id']) && ! isset($this->payload['visitor_id'])) {
            throw new \RuntimeException('Payload deve ter event_id ou visitor_id');
        }

        return true;
    }

    public function handle(): void
    {
        $this->validatePayload();

        $response = Http::baseUrl(rtrim((string) config('customer-intelligence.base_url'), '/'))
            ->withToken((string) config('customer-intelligence.token'))
            ->timeout((int) config('customer-intelligence.timeout', 5))
            ->acceptJson()
            ->post($this->endpoint, $this->payload);

        if ($response->successful()) {
            Log::debug('JMF Customer Intelligence: payload enviado com sucesso.', [
                'endpoint' => $this->endpoint,
                'event_id' => $this->payload['event_id'] ?? null,
                'status' => $response->status(),
            ]);

            return;
        }

        $this->handleErrorResponse($response);
    }

    /**
     * Trata resposta de erro de forma inteligente.
     *
     * Erros 4xx (exceto 429): não retry, apenas log
     * Erros 5xx ou 429: retry via exception
     */
    private function handleErrorResponse($response): void
    {
        $status = $response->status();
        $isRetryable = $response->serverError() || $status === 429;

        $context = [
            'endpoint' => $this->endpoint,
            'status' => $status,
            'event_id' => $this->payload['event_id'] ?? null,
            'body' => $response->json() ?? $response->body(),
            'attempt' => $this->attempts(),
            'retryable' => $isRetryable,
        ];

        if ($isRetryable) {
            Log::warning('JMF Customer Intelligence: erro transitório, agendando retry.', $context);
            throw new RequestException($response);
        }

        Log::warning('JMF Customer Intelligence: payload rejeitado pela API (erro de cliente).', $context);
    }

    public function failed(Throwable $exception): void
    {
        Log::error('JMF Customer Intelligence: falha ao enviar payload após todas as tentativas.', [
            'endpoint' => $this->endpoint,
            'event_id' => $this->payload['event_id'] ?? null,
            'attempts' => $this->attempts(),
            'exception' => $exception->getMessage(),
        ]);
    }
}
