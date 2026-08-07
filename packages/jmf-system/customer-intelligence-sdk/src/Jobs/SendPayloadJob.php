<?php

namespace JmfSystem\CustomerIntelligence\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use JmfSystem\CustomerIntelligence\PayloadLogger;
use JmfSystem\CustomerIntelligence\PayloadValidator;
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
 * - Erros de rede: retry com exponential backoff
 */
class SendPayloadJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public int $tries;

    private PayloadLogger $logger;

    /**
     * @param  'events'|'contacts/identify'  $endpoint
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        public readonly string $endpoint,
        public readonly array $payload,
    ) {
        $this->tries = (int) config('customer-intelligence.tries', 3);
        $this->logger = new PayloadLogger($endpoint, $payload);
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

    public function handle(): void
    {
        try {
            PayloadValidator::validate($this->endpoint, $this->payload);
        } catch (\RuntimeException $e) {
            $this->logger->validationError($e->getMessage());

            throw $e;
        }

        try {
            $startTime = microtime(true);

            $response = Http::baseUrl(rtrim((string) config('customer-intelligence.base_url'), '/'))
                ->withToken((string) config('customer-intelligence.token'))
                ->timeout((int) config('customer-intelligence.timeout', 5))
                ->acceptJson()
                ->post($this->endpoint, $this->payload);

            $duration = microtime(true) - $startTime;

            if ($response->successful()) {
                $this->logger->success($response->status(), $duration);

                return;
            }

            $this->handleErrorResponse($response);
        } catch (ConnectionException $e) {
            $this->handleNetworkError($e);
        }
    }

    /**
     * Trata resposta de erro HTTP de forma inteligente.
     *
     * Erros 4xx (exceto 429): não retry, apenas log
     * Erros 5xx ou 429: retry via exception
     */
    private function handleErrorResponse($response): void
    {
        $status = $response->status();
        $isRetryable = $response->serverError() || $status === 429;
        $reason = match (true) {
            $status === 429 => 'Rate limit atingido',
            $response->serverError() => 'Erro do servidor',
            $status === 400 => 'Requisição malformada',
            $status === 401 => 'Token de autenticação inválido ou expirado',
            $status === 403 => 'Acesso negado',
            $status === 404 => 'Endpoint não encontrado',
            $status === 422 => 'Validação falhada: ' . json_encode($response->json() ?? []),
            default => "HTTP $status",
        };

        if ($isRetryable) {
            $this->logger->retryable($status, $this->attempts(), $this->tries, $reason);
            throw new RequestException($response);
        }

        $this->logger->permanent($status, $reason);
    }

    /**
     * Trata erros de rede (timeout, conexão recusada, etc).
     *
     * Sempre retentável — erros de rede são transitórios.
     */
    private function handleNetworkError(ConnectionException $e): void
    {
        $reason = match (true) {
            str_contains($e->getMessage(), 'timed out') => 'Timeout na conexão',
            str_contains($e->getMessage(), 'Connection refused') => 'Conexão recusada',
            str_contains($e->getMessage(), 'Unreachable') => 'Host inacessível',
            default => $e->getMessage(),
        };

        $this->logger->networkError($reason, $this->attempts());

        throw $e;
    }

    public function failed(Throwable $exception): void
    {
        $this->logger->failed($this->attempts(), $exception);
    }
}
