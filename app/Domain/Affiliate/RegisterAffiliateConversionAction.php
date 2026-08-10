<?php

namespace App\Domain\Affiliate;

use App\Models\AffiliateConversion;
use Carbon\Carbon;

class RegisterAffiliateConversionAction
{
    public function execute(array $data): AffiliateConversion
    {
        return AffiliateConversion::create([
            'application_id' => $data['application_id'],
            'affiliate_product_id' => $data['affiliate_product_id'],
            'affiliate_program_id' => $data['affiliate_program_id'],
            'campaign_id' => $data['campaign_id'] ?? null,
            'affiliate_link_id' => $data['affiliate_link_id'] ?? null,
            'order_reference' => $data['order_reference'],
            'order_date' => Carbon::parse($data['order_date']),
            'product_price' => $data['product_price'] ?? 0,
            'commission_rate' => $data['commission_rate'] ?? 0,
            'commission_value' => $data['commission_value'] ?? 0,
            'status' => $data['status'] ?? AffiliateConversion::STATUS_PENDING,
            'notes' => $data['notes'] ?? null,
        ]);
    }
}
