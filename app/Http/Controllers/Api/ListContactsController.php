<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Contact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ListContactsController extends Controller
{
    /**
     * Lista paginada de contatos do tenant da Application autenticada,
     * consumida pelo componente Livewire ContactIndex do SDK cliente.
     *
     * Contact não tem application_id (é compartilhado entre as
     * Applications de um mesmo Tenant), por isso o escopo aqui é por
     * tenant_id, mesmo padrão usado pelo restante do domínio de contatos.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $application = $request->user('sanctum');

        abort_unless($application instanceof Application, 401);

        $perPage = min((int) $request->query('per_page', 25), 100);
        $page = max((int) $request->query('page', 1), 1);

        $paginator = Contact::query()
            ->where('tenant_id', $application->tenant_id)
            ->withMax('events as last_event_at', 'occurred_at')
            ->when($request->query('search'), fn ($query, $search) => $query->where(function ($query) use ($search) {
                $query->where('email', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            }))
            ->when($request->query('start_date'), fn ($query, $startDate) => $query->where('last_seen_at', '>=', Carbon::parse($startDate)->startOfDay()))
            ->when($request->query('end_date'), fn ($query, $endDate) => $query->where('last_seen_at', '<=', Carbon::parse($endDate)->endOfDay()))
            ->orderByDesc('last_seen_at')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'data' => $paginator->getCollection()->map(fn (Contact $contact) => [
                'id' => $contact->id,
                'email' => $contact->email,
                'name' => $contact->name,
                'lead_score' => $contact->lead_score,
                'last_event_at' => $contact->last_event_at,
            ])->all(),
            'total' => $paginator->total(),
            'per_page' => $paginator->perPage(),
            'current_page' => $paginator->currentPage(),
        ]);
    }
}
