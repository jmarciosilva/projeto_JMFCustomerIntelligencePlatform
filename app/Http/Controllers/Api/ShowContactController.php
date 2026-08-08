<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Contact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShowContactController extends Controller
{
    /**
     * Detalhe de um contato, consumido pelo componente Livewire ContactShow
     * do SDK cliente. Aborta 404 (em vez de 403) se o contato pertence a
     * outro tenant, para não confirmar a existência do registro.
     */
    public function __invoke(Request $request, Contact $contact): JsonResponse
    {
        $application = $request->user('sanctum');

        abort_unless($application instanceof Application, 401);
        abort_unless($contact->tenant_id === $application->tenant_id, 404);

        return response()->json([
            'id' => $contact->id,
            'external_id' => $contact->external_id,
            'email' => $contact->email,
            'phone' => $contact->phone,
            'name' => $contact->name,
            'lead_score' => $contact->lead_score,
            'properties' => $contact->properties,
            'first_identified_at' => $contact->first_identified_at?->toIso8601String(),
            'last_seen_at' => $contact->last_seen_at?->toIso8601String(),
        ]);
    }
}
