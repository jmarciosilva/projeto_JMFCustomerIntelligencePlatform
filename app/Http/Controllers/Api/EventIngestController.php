<?php

namespace App\Http\Controllers\Api;

use App\Application\Events\Actions\IngestEventAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreEventRequest;
use App\Models\Application;
use Illuminate\Http\JsonResponse;

class EventIngestController extends Controller
{
    public function __construct(private readonly IngestEventAction $action) {}

    /**
     * Handle the incoming request.
     */
    public function __invoke(StoreEventRequest $request): JsonResponse
    {
        $application = $request->user('sanctum');

        abort_unless($application instanceof Application, 401);

        $result = $this->action->handle($application, $request->validated());

        return response()->json($result, $result['status'] === 'accepted' ? 202 : 200);
    }
}
