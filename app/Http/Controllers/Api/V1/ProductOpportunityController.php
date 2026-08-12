<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Affiliate\Actions\ApproveProductOpportunityAction;
use App\Domain\Affiliate\Actions\CreateProductOpportunityAction;
use App\Domain\Affiliate\Actions\PublishProductOpportunityAction;
use App\Domain\Affiliate\Actions\RejectProductOpportunityAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProductOpportunity\ApproveProductOpportunityRequest;
use App\Http\Requests\ProductOpportunity\CreateProductOpportunityRequest;
use App\Http\Requests\ProductOpportunity\RejectProductOpportunityRequest;
use App\Http\Resources\ProductOpportunityResource;
use App\Models\ProductOpportunity;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ProductOpportunityController extends Controller
{
    public function __construct(
        private CreateProductOpportunityAction $createAction,
        private ApproveProductOpportunityAction $approveAction,
        private RejectProductOpportunityAction $rejectAction,
        private PublishProductOpportunityAction $publishAction,
    ) {}

    public function index(Request $request): ResourceCollection
    {
        $application = $this->getApplication($request);
        $query = ProductOpportunity::byApplication($application->id);

        if ($request->has('status')) {
            $query->byStatus($request->get('status'));
        }

        if ($request->has('sort')) {
            $sort = $request->get('sort');
            $order = str_starts_with($sort, '-') ? 'desc' : 'asc';
            $column = ltrim($sort, '-');
            $query->orderBy($column, $order);
        } else {
            $query->recentFirst();
        }

        $opportunities = $query->paginate($request->get('per_page', 15));

        return ProductOpportunityResource::collection($opportunities);
    }

    public function show(ProductOpportunity $opportunity, Request $request): ProductOpportunityResource
    {
        $application = $this->getApplication($request);
        $this->authorizeApplication($opportunity, $application);

        return new ProductOpportunityResource($opportunity);
    }

    public function store(CreateProductOpportunityRequest $request): ProductOpportunityResource
    {
        $application = $this->getApplication($request);

        $opportunity = $this->createAction->execute(
            applicationId: $application->id,
            trend: $request->getTrend(),
            affiliateProduct: $request->getAffiliateProduct(),
            discoveryOpportunityScore: $request->input('discovery_opportunity_score'),
            opportunityScoreBreakdown: $request->input('opportunity_score_breakdown', []),
            purchaseIntentScore: $request->input('purchase_intent_score'),
            purchaseIntentLabel: $request->input('purchase_intent_label'),
            purchaseIntentBreakdown: $request->input('purchase_intent_breakdown', []),
        );

        return new ProductOpportunityResource($opportunity);
    }

    public function approve(
        ProductOpportunity $opportunity,
        ApproveProductOpportunityRequest $request,
    ): ProductOpportunityResource {
        $application = $this->getApplication($request);
        $this->authorizeApplication($opportunity, $application);
        $user = User::factory()->create();

        $opportunity = $this->approveAction->execute(
            opportunity: $opportunity,
            approver: $user,
            reason: $request->input('reason'),
        );

        return new ProductOpportunityResource($opportunity);
    }

    public function reject(
        ProductOpportunity $opportunity,
        RejectProductOpportunityRequest $request,
    ): ProductOpportunityResource {
        $application = $this->getApplication($request);
        $this->authorizeApplication($opportunity, $application);
        $user = User::factory()->create();

        $opportunity = $this->rejectAction->execute(
            opportunity: $opportunity,
            rejecter: $user,
            reason: $request->input('reason'),
        );

        return new ProductOpportunityResource($opportunity);
    }

    public function publish(ProductOpportunity $opportunity, Request $request): ProductOpportunityResource
    {
        $application = $this->getApplication($request);
        $this->authorizeApplication($opportunity, $application);

        $opportunity = $this->publishAction->execute($opportunity);

        return new ProductOpportunityResource($opportunity);
    }

    private function getApplication(Request $request)
    {
        return $request->user('sanctum');
    }

    private function authorizeApplication(ProductOpportunity $opportunity, $application): void
    {
        if ($opportunity->application_id !== $application->id) {
            abort(403, 'Unauthorized application');
        }
    }
}
