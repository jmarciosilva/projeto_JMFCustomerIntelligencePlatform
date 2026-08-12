<?php

namespace App\Jobs;

use App\Models\AffiliateProduct;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ValidateAffiliateProductDataJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public function __construct(private AffiliateProduct $product) {}

    public function handle(): void
    {
        try {
            $this->product->update([
                'price_checked_at' => now(),
                'availability_checked_at' => now(),
            ]);

            Log::info('Affiliate product data validated', [
                'product_id' => $this->product->id,
                'application_id' => $this->product->application_id,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to validate affiliate product data', [
                'product_id' => $this->product->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
