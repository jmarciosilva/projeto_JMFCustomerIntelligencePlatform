<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PingController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $application = $request->user('sanctum');

        abort_unless($application instanceof Application, 401);

        return response()->json([
            'tenant' => [
                'id' => $application->tenant->id,
                'name' => $application->tenant->name,
            ],
            'application' => [
                'id' => $application->id,
                'name' => $application->name,
            ],
        ]);
    }
}
