<?php

namespace App\Http\Controllers\Api\Marketplace;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\BusinessRecommendation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SellerRecommendationsController extends Controller
{
    private const VALID_TYPES = [
        BusinessRecommendation::TYPE_SALES_DROP,
        BusinessRecommendation::TYPE_KIT_OPPORTUNITY,
        BusinessRecommendation::TYPE_PRICE_OUTLIER,
        BusinessRecommendation::TYPE_IDEAL_TIMING,
    ];

    public function __invoke(Request $request, int $sellerId): JsonResponse
    {
        $application = $request->user('sanctum');

        abort_unless($application instanceof Application, 401);

        $query = BusinessRecommendation::where('application_id', $application->id)
            ->forSeller($sellerId)
            ->orderByDesc('priority');

        if ($request->filled('type')) {
            $type = $request->get('type');
            abort_unless(in_array($type, self::VALID_TYPES, true), 422, 'Tipo de recomendação inválido.');
            $query->where('type', $type);
        }

        $limit = min((int) $request->get('limit', 20), 100);

        $recommendations = $query->limit($limit)->get([
            'id', 'type', 'priority', 'title', 'message', 'data', 'generated_at',
        ]);

        return response()->json([
            'seller_id' => $sellerId,
            'data' => $recommendations,
            'total' => $recommendations->count(),
        ]);
    }
}
