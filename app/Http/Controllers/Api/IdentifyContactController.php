<?php

namespace App\Http\Controllers\Api;

use App\Application\Identity\Actions\IdentifyContactAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\IdentifyContactRequest;
use App\Models\Application;
use Illuminate\Http\JsonResponse;

class IdentifyContactController extends Controller
{
    public function __construct(private readonly IdentifyContactAction $action) {}

    /**
     * Handle the incoming request.
     */
    public function __invoke(IdentifyContactRequest $request): JsonResponse
    {
        $application = $request->user('sanctum');

        abort_unless($application instanceof Application, 401);

        $contact = $this->action->handle($application, $request->validated());

        return response()->json([
            'contact_id' => $contact->id,
            'external_id' => $contact->external_id,
            'email' => $contact->email,
        ]);
    }
}
