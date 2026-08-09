<?php

namespace App\Application\Affiliate\Actions;

use App\Models\AffiliateProduct;
use App\Support\Audit\AuditLogger;

class DeleteAffiliateProductAction
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function handle(AffiliateProduct $product): void
    {
        $this->auditLogger->log('affiliate_product.deleted', $product, [
            'name' => $product->name,
            'affiliate_program_id' => $product->affiliate_program_id,
        ]);

        $product->delete();
    }
}
