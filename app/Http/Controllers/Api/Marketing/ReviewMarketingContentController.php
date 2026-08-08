<?php

namespace App\Http\Controllers\Api\Marketing;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\MarketingContent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewMarketingContentController extends Controller
{
    public function __invoke(Request $request, MarketingContent $marketingContent): JsonResponse
    {
        $application = $request->user('sanctum');

        abort_unless($application instanceof Application, 401);
        abort_unless($marketingContent->application_id === $application->id, 404);

        $data = $request->validate([
            'status' => ['required', 'string', 'in:approved,rejected'],
            'content' => ['nullable', 'string', 'max:5000'],
        ]);

        $marketingContent->update([
            'status' => $data['status'],
            'content' => $data['content'] ?? $marketingContent->content,
            'reviewed_at' => now(),
        ]);

        return response()->json(['data' => $marketingContent->fresh()]);
    }
}
