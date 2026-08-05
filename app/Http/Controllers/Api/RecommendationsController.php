<?php

namespace App\Http\Controllers\Api;

use App\Application\Intelligence\Actions\GetRecommendationsAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\GetRecommendationsRequest;
use App\Models\Application;
use Illuminate\Http\JsonResponse;

class RecommendationsController extends Controller
{
    public function __construct(private readonly GetRecommendationsAction $action) {}

    /**
     * Handle the incoming request.
     */
    public function __invoke(GetRecommendationsRequest $request): JsonResponse
    {
        $application = $request->user('sanctum');

        abort_unless($application instanceof Application, 401);

        $data = $request->validated();

        $recommendations = $this->action->handle(
            $application,
            $data['subject_type'],
            $data['subject_id'],
            $data['limit'] ?? 5,
        );

        return response()->json(['recommendations' => $recommendations]);
    }
}
