<?php

namespace App\Console\Commands;

use App\Jobs\ValidateAffiliateProductDataJob;
use App\Models\AffiliateProduct;
use Illuminate\Console\Command;

class ValidateAffiliateProductDataCommand extends Command
{
    protected $signature = 'affiliate:validate-product-data {--application= : Application ID to validate}';

    protected $description = 'Validate affiliate product data (price, availability) by dispatching validation jobs';

    public function handle(): int
    {
        $applicationId = $this->option('application');

        $query = AffiliateProduct::query();

        if ($applicationId) {
            $query->where('application_id', $applicationId);
        }

        $products = $query->get();

        if ($products->isEmpty()) {
            $this->info('No affiliate products found to validate.');

            return self::SUCCESS;
        }

        foreach ($products as $product) {
            ValidateAffiliateProductDataJob::dispatch($product);
        }

        $this->info("Dispatched {$products->count()} validation jobs for affiliate products.");

        return self::SUCCESS;
    }
}
