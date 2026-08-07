<?php

namespace JmfSystem\CustomerIntelligence;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PayloadLogger
{
    /**
     * Contexto estruturado para logs do SDK.
     *
     * @var array<string, mixed>
     */
    private array $context = [];

    /**
     * Trace ID único para rastrear uma requisição ponta-a-ponta.
     */
    private readonly string $traceId;

    /**
     * @param  'events'|'contacts/identify'  $endpoint
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        private readonly string $endpoint,
        private readonly array $payload,
    ) {
        $this->traceId = Str::ulid();
        $this->buildContext();
    }

    /**
     * Constrói contexto estruturado base.
     */
    private function buildContext(): void
    {
        $this->context = [
            'trace_id' => $this->traceId,
            'endpoint' => $this->endpoint,
            'event_id' => $this->payload['event_id'] ?? null,
            'visitor_id' => $this->payload['visitor_id'] ?? null,
            'timestamp' => now()->toIso8601String(),
            'sdk_version' => '1.0.0', // TODO: Read from package.json or constant
        ];
    }

    /**
     * Log de sucesso ao enviar payload.
     */
    public function success(int $statusCode, float $duration): void
    {
        Log::info('JMF Customer Intelligence: payload enviado com sucesso', array_merge(
            $this->context,
            [
                'status' => $statusCode,
                'duration_ms' => round($duration * 1000, 2),
                'size_bytes' => strlen(json_encode($this->payload) ?: '{}'),
            ]
        ));
    }

    /**
     * Log de erro transitório (será retentado).
     */
    public function retryable(int $statusCode, int $attempt, int $maxAttempts, string $reason = ''): void
    {
        Log::warning('JMF Customer Intelligence: erro transitório, agendando retry', array_merge(
            $this->context,
            [
                'status' => $statusCode,
                'attempt' => $attempt,
                'max_attempts' => $maxAttempts,
                'reason' => $reason,
                'next_retry_in_seconds' => $this->getRetryDelay($attempt),
            ]
        ));
    }

    /**
     * Log de erro permanente (não será retentado).
     */
    public function permanent(int $statusCode, string $reason = ''): void
    {
        Log::warning('JMF Customer Intelligence: erro permanente, desistindo', array_merge(
            $this->context,
            [
                'status' => $statusCode,
                'reason' => $reason,
                'response_body_preview' => substr($reason, 0, 500),
            ]
        ));
    }

    /**
     * Log de falha final após todas as tentativas.
     */
    public function failed(int $attempts, \Throwable $exception): void
    {
        Log::error('JMF Customer Intelligence: falha ao enviar após todas as tentativas', array_merge(
            $this->context,
            [
                'attempts' => $attempts,
                'exception_class' => get_class($exception),
                'exception_message' => $exception->getMessage(),
                'stack_trace_file' => $exception->getFile(),
                'stack_trace_line' => $exception->getLine(),
            ]
        ));
    }

    /**
     * Log de erro de validação.
     */
    public function validationError(string $reason): void
    {
        Log::error('JMF Customer Intelligence: erro de validação do payload', array_merge(
            $this->context,
            [
                'reason' => $reason,
                'payload_debug' => PayloadValidator::debug($this->payload),
            ]
        ));
    }

    /**
     * Log de erro de rede.
     */
    public function networkError(string $reason, int $attempt): void
    {
        Log::warning('JMF Customer Intelligence: erro de rede, agendando retry', array_merge(
            $this->context,
            [
                'reason' => $reason,
                'attempt' => $attempt,
                'next_retry_in_seconds' => $this->getRetryDelay($attempt),
            ]
        ));
    }

    /**
     * Log de configuração inválida.
     */
    public function configError(string $reason): void
    {
        Log::error('JMF Customer Intelligence: erro de configuração', [
            'reason' => $reason,
            'trace_id' => $this->traceId,
        ]);
    }

    /**
     * Retorna trace ID para correlação.
     */
    public function getTraceId(): string
    {
        return $this->traceId;
    }

    /**
     * Calcula delay de retry baseado no número de tentativas.
     *
     * Usa exponential backoff: 5s, 30s, 120s
     */
    private function getRetryDelay(int $attempt): int
    {
        $backoff = config('customer-intelligence.backoff', [5, 30, 120]);

        return $backoff[$attempt - 1] ?? end($backoff);
    }
}
