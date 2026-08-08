<?php

namespace App\Http\Controllers\Api\Marketplace;

use App\Domain\Marketplace\GetSellerAnalyticsAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SellerAnalyticsController extends Controller
{
    public function __invoke(Request $request, int $sellerId, GetSellerAnalyticsAction $action): JsonResponse
    {
        $days = $request->get('days', 7);

        $analytics = $action->handle(
            $request->user()->id,
            $sellerId,
            (int) $days
        );

        return response()->json($analytics);
    }
}
