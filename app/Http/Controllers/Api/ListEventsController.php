<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ListEventsController extends Controller
{
    /**
     * Lista paginada de eventos da Application autenticada, consumida pelo
     * componente Livewire EventIndex do SDK cliente.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $application = $request->user('sanctum');

        abort_unless($application instanceof Application, 401);

        $perPage = min((int) $request->query('per_page', 50), 100);
        $page = max((int) $request->query('page', 1), 1);

        $paginator = Event::query()
            ->where('application_id', $application->id)
            ->with('contact:id,email')
            ->when($request->query('event_name'), fn ($query, $eventName) => $query->where('event_name', 'like', "%{$eventName}%"))
            ->when($request->query('search'), fn ($query, $search) => $query->where('visitor_id', 'like', "%{$search}%"))
            ->when($request->query('start_date'), fn ($query, $startDate) => $query->where('occurred_at', '>=', Carbon::parse($startDate)->startOfDay()))
            ->when($request->query('end_date'), fn ($query, $endDate) => $query->where('occurred_at', '<=', Carbon::parse($endDate)->endOfDay()))
            ->orderByDesc('occurred_at')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'data' => $paginator->getCollection()->map(fn (Event $event) => [
                'event_name' => $event->event_name,
                'visitor_id' => $event->visitor_id,
                'contact_email' => $event->contact?->email,
                'occurred_at' => $event->occurred_at?->toIso8601String(),
                'properties' => $event->properties,
            ])->all(),
            'total' => $paginator->total(),
            'per_page' => $paginator->perPage(),
            'current_page' => $paginator->currentPage(),
        ]);
    }
}
