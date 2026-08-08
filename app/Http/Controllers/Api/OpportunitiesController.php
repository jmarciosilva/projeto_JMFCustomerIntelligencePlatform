<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Opportunity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OpportunitiesController extends Controller
{
    private const TYPE_MAP = [
        'cross-sell' => Opportunity::TYPE_CROSS_SELL,
        'up-sell' => Opportunity::TYPE_UP_SELL,
        'win-back' => Opportunity::TYPE_WIN_BACK,
        'bundles' => Opportunity::TYPE_BUNDLE,
    ];

    public function __invoke(Request $request, string $type): JsonResponse
    {
        $application = $request->user('sanctum');

        abort_unless($application instanceof Application, 401);
        abort_unless(array_key_exists($type, self::TYPE_MAP), 404, 'Tipo de oportunidade inválido.');

        $query = Opportunity::where('application_id', $application->id)
            ->ofType(self::TYPE_MAP[$type])
            ->orderByDesc('score');

        if ($request->filled('product_id')) {
            $query->where('product_id', (int) $request->get('product_id'));
        }

        $limit = min((int) $request->get('limit', 20), 100);

        $opportunities = $query->limit($limit)->get([
            'id', 'contact_id', 'product_id', 'related_product_id',
            'score', 'potential_value', 'reason', 'detected_at',
        ]);

        return response()->json([
            'type' => $type,
            'data' => $opportunities,
            'total' => $opportunities->count(),
        ]);
    }
}
