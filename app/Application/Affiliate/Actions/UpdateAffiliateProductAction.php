<?php

namespace App\Application\Affiliate\Actions;

use App\Models\AffiliateProduct;
use App\Support\Audit\AuditLogger;

class UpdateAffiliateProductAction
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(AffiliateProduct $product, array $data): AffiliateProduct
    {
        $before = $product->only(['name', 'price', 'commission_percentage', 'availability']);

        $product->fill([
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
            'availability' => $data['availability'] ?? $product->availability,
            'last_checked_at' => now(),
        ]);
        $product->save();

        $this->auditLogger->log('affiliate_product.updated', $product, [
            'before' => $before,
            'after' => $product->only(['name', 'price', 'commission_percentage', 'availability']),
        ]);

        return $product;
    }
}
