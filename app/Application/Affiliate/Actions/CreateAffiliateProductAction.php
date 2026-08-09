<?php

namespace App\Application\Affiliate\Actions;

use App\Models\AffiliateProduct;
use App\Models\AffiliateProgram;
use App\Support\Audit\AuditLogger;

class CreateAffiliateProductAction
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(AffiliateProgram $program, array $data): AffiliateProduct
    {
        $product = AffiliateProduct::create([
            'application_id' => $program->application_id,
            'affiliate_program_id' => $program->id,
            'external_product_id' => $data['external_product_id'] ?? null,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'category' => $data['category'] ?? null,
            'brand' => $data['brand'] ?? null,
            'price' => $data['price'] ?? null,
            'original_price' => $data['original_price'] ?? null,
            'commission_percentage' => $data['commission_percentage'] ?? null,
            'estimated_commission' => $data['estimated_commission'] ?? null,
            'affiliate_url' => $data['affiliate_url'],
            'image_url' => $data['image_url'] ?? null,
            'availability' => $data['availability'] ?? AffiliateProduct::AVAILABILITY_UNKNOWN,
            'last_checked_at' => now(),
        ]);

        $this->auditLogger->log('affiliate_product.created', $product, [
            'name' => $product->name,
            'affiliate_program_id' => $program->id,
        ]);

        return $product;
    }
}
