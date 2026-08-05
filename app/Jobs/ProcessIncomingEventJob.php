<?php

namespace App\Jobs;

use App\Events\EventWasIngested;
use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessIncomingEventJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    /**
     * @param  array<string, mixed>  $data  Payload validado, já incluindo `received_at`.
     */
    public function __construct(
        public readonly int $applicationId,
        public readonly int $tenantId,
        public readonly array $data,
    ) {}

    public function handle(): void
    {
        try {
            $event = Event::query()->create([
                'tenant_id' => $this->tenantId,
                'application_id' => $this->applicationId,
                'event_id' => $this->data['event_id'],
                'event_name' => $this->data['event_name'],
                'visitor_id' => $this->data['visitor_id'],
                'session_id' => $this->data['session_id'] ?? null,
                'contact_id' => $this->data['contact_id'] ?? null,
                'subject_type' => $this->data['subject_type'] ?? null,
                'subject_id' => $this->data['subject_id'] ?? null,
                'properties' => $this->data['properties'] ?? null,
                'context' => $this->data['context'] ?? null,
                'occurred_at' => $this->data['occurred_at'],
                'received_at' => $this->data['received_at'],
            ]);

            event(new EventWasIngested($event));
        } catch (QueryException $exception) {
            // SQLSTATE 23000: violação de unique(application_id, event_id) — duas
            // requisições concorrentes com o mesmo event_id passaram pela checagem
            // de idempotência da Action antes de qualquer uma delas persistir.
            // O evento já foi gravado pela outra execução; nada a fazer aqui.
            if ($exception->getCode() === '23000') {
                Log::warning('Evento duplicado descartado durante o processamento assíncrono.', [
                    'application_id' => $this->applicationId,
                    'event_id' => $this->data['event_id'],
                ]);

                return;
            }

            throw $exception;
        }
    }

    public function failed(Throwable $exception): void
    {
        Log::error('Falha ao processar evento recebido.', [
            'application_id' => $this->applicationId,
            'tenant_id' => $this->tenantId,
            'event_id' => $this->data['event_id'] ?? null,
            'exception' => $exception->getMessage(),
        ]);
    }
}
