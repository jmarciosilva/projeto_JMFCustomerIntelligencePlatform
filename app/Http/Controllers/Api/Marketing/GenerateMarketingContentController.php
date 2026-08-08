<?php

namespace App\Http\Controllers\Api\Marketing;

use App\Actions\GenerateEmailCampaignAction;
use App\Actions\GenerateProductContentAction;
use App\Actions\GenerateSocialContentAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\GenerateMarketingContentRequest;
use App\Models\Application;
use Illuminate\Http\JsonResponse;

class GenerateMarketingContentController extends Controller
{
    public function __construct(
        private GenerateProductContentAction $productContentAction,
        private GenerateSocialContentAction $socialContentAction,
        private GenerateEmailCampaignAction $emailCampaignAction,
    ) {}

    public function __invoke(GenerateMarketingContentRequest $request): JsonResponse
    {
        $application = $request->user('sanctum');

        abort_unless($application instanceof Application, 401);

        $data = $request->validated();
        $subjectType = $data['subject_type'];
        $subjectId = $data['subject_id'];
        $product = $data['product'];

        $productContent = $this->productContentAction->execute($application->id, $subjectType, $subjectId, $product);
        $socialContent = $this->socialContentAction->execute($application->id, $subjectType, $subjectId, $product);
        $emailContent = $this->emailCampaignAction->execute($application->id, $subjectType, $subjectId, $product);

        $all = array_merge($productContent, $socialContent, [$emailContent]);

        return response()->json([
            'data' => $all,
            'total' => count($all),
        ], 201);
    }
}
