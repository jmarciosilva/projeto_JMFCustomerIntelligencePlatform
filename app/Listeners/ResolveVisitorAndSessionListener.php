<?php

namespace App\Listeners;

use App\Events\EventWasIngested;
use App\Models\Visitor;
use App\Models\VisitorSession;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Throwable;

class ResolveVisitorAndSessionListener implements ShouldQueue
{
    public int $tries = 3;

    public function handle(EventWasIngested $ingested): void
    {
        $event = $ingested->event;
        $occurredAt = Carbon::parse($event->occurred_at);

        $visitor = Visitor::query()->firstOrNew([
            'application_id' => $event->application_id,
            'visitor_id' => $event->visitor_id,
        ]);

        if (! $visitor->exists) {
            $visitor->tenant_id = $event->tenant_id;
            $visitor->first_seen_at = $occurredAt;
        }

        if (! $visitor->last_seen_at || $occurredAt->greaterThan($visitor->last_seen_at)) {
            $visitor->last_seen_at = $occurredAt;
        }

        $visitor->save();

        if ($event->session_id === null) {
            return;
        }

        $session = VisitorSession::query()->firstOrNew([
            'application_id' => $event->application_id,
            'session_id' => $event->session_id,
        ]);

        if (! $session->exists) {
            $session->tenant_id = $event->tenant_id;
            $session->visitor_id = $visitor->id;
            $session->started_at = $occurredAt;
        }

        if (! $session->last_seen_at || $occurredAt->greaterThan($session->last_seen_at)) {
            $session->last_seen_at = $occurredAt;
        }

        $session->save();
    }

    public function failed(EventWasIngested $ingested, Throwable $exception): void
    {
        Log::error('Falha ao resolver visitante/sessão a partir de um evento.', [
            'event_id' => $ingested->event->event_id,
            'application_id' => $ingested->event->application_id,
            'exception' => $exception->getMessage(),
        ]);
    }
}
