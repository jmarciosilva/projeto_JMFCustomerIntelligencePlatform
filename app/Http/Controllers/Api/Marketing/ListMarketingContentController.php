<?php

namespace App\Http\Controllers\Api\Marketing;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\MarketingContent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListMarketingContentController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $application = $request->user('sanctum');

        abort_unless($application instanceof Application, 401);

        $request->validate([
            'subject_type' => ['required', 'string'],
            'subject_id' => ['required', 'integer'],
            'status' => ['nullable', 'string', 'in:draft,approved,rejected'],
            'type' => ['nullable', 'string'],
        ]);

        $query = MarketingContent::where('application_id', $application->id)
            ->forSubject($request->get('subject_type'), (int) $request->get('subject_id'))
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->filled('type')) {
            $query->where('type', $request->get('type'));
        }

        $content = $query->get();

        return response()->json([
            'data' => $content,
            'total' => $content->count(),
        ]);
    }
}
