<?php

namespace App\Http\Requests\ProductOpportunity;

use App\Models\AffiliateProduct;
use App\Models\Trend;
use Illuminate\Foundation\Http\FormRequest;

class CreateProductOpportunityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->application_id !== null;
    }

    public function rules(): array
    {
        return [
            'trend_id' => 'required|exists:trends,id',
            'affiliate_product_id' => 'required|exists:affiliate_products,id',
            'discovery_opportunity_score' => 'required|integer|min:0|max:100',
            'opportunity_score_breakdown' => 'sometimes|array',
            'purchase_intent_score' => 'required|integer|min:0|max:100',
            'purchase_intent_label' => 'required|string|in:LOW,MEDIUM,HIGH',
            'purchase_intent_breakdown' => 'sometimes|array',
        ];
    }

    public function getTrend(): Trend
    {
        return Trend::findOrFail($this->input('trend_id'));
    }

    public function getAffiliateProduct(): AffiliateProduct
    {
        return AffiliateProduct::findOrFail($this->input('affiliate_product_id'));
    }
}
