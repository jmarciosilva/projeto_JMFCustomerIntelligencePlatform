<?php

namespace App\Application\Events\Actions;

use App\Jobs\ProcessIncomingEventJob;
use App\Models\Application;
use App\Models\Event;

class IngestEventAction
{
    /**
     * @param  array<string, mixed>  $data  Payload já validado (StoreEventRequest::validated()).
     * @return array{status: 'accepted'|'duplicate', event_id: string}
     */
    public function handle(Application $application, array $data): array
    {
        $alreadyExists = Event::query()
            ->where('application_id', $application->id)
            ->where('event_id', $data['event_id'])
            ->exists();

        if ($alreadyExists) {
            return ['status' => 'duplicate', 'event_id' => $data['event_id']];
        }

        // received_at é capturado aqui (no momento da ingestão HTTP), não dentro
        // do Job, para refletir o instante real de recebimento mesmo que o
        // processamento assíncrono seja postergado pela fila.
        $data['received_at'] = now();

        ProcessIncomingEventJob::dispatch($application->id, $application->tenant_id, $data);

        return ['status' => 'accepted', 'event_id' => $data['event_id']];
    }
}
