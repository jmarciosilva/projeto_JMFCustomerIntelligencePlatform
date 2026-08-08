<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Contact;
use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListContactEventsController extends Controller
{
    /**
     * Eventos de um contato específico, consumido pelo componente Livewire
     * ContactShow do SDK cliente. Escopado tanto pelo contato quanto pela
     * Application autenticada, para não misturar eventos de outras
     * Applications do mesmo tenant.
     */
    public function __invoke(Request $request, Contact $contact): JsonResponse
    {
        $application = $request->user('sanctum');

        abort_unless($application instanceof Application, 401);
        abort_unless($contact->tenant_id === $application->tenant_id, 404);

        $perPage = min((int) $request->query('per_page', 25), 100);
        $page = max((int) $request->query('page', 1), 1);

        $paginator = Event::query()
            ->where('contact_id', $contact->id)
            ->where('application_id', $application->id)
            ->orderByDesc('occurred_at')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'data' => $paginator->getCollection()->map(fn (Event $event) => [
                'event_name' => $event->event_name,
                'visitor_id' => $event->visitor_id,
                'occurred_at' => $event->occurred_at?->toIso8601String(),
                'properties' => $event->properties,
            ])->all(),
            'total' => $paginator->total(),
            'per_page' => $paginator->perPage(),
            'current_page' => $paginator->currentPage(),
        ]);
    }
}
