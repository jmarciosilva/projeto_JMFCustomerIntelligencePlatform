<?php

namespace Tests\Unit\Domain\Affiliate;

use App\Domain\Affiliate\ProductDataValidationService;
use App\Models\AffiliateProduct;
use Tests\TestCase;

class ProductDataValidationServiceTest extends TestCase
{
    private ProductDataValidationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ProductDataValidationService(stalenessThresholdDays: 7);
    }

    public function test_product_with_no_checked_timestamps_is_stale(): void
    {
        $product = AffiliateProduct::factory()->create([
            'price_checked_at' => null,
            'availability_checked_at' => null,
        ]);

        $this->assertTrue($this->service->isDataStale($product));
        $this->assertFalse($this->service->isDataFresh($product));
    }

    public function test_product_with_recent_checks_is_fresh(): void
    {
        $product = AffiliateProduct::factory()->create([
            'price_checked_at' => now()->subDays(2),
            'availability_checked_at' => now()->subDays(2),
        ]);

        $this->assertFalse($this->service->isDataStale($product));
        $this->assertTrue($this->service->isDataFresh($product));
    }

    public function test_product_with_old_price_check_is_stale(): void
    {
        $product = AffiliateProduct::factory()->create([
            'price_checked_at' => now()->subDays(10),
            'availability_checked_at' => now()->subDays(2),
        ]);

        $this->assertTrue($this->service->isDataStale($product));
    }

    public function test_product_with_old_availability_check_is_stale(): void
    {
        $product = AffiliateProduct::factory()->create([
            'price_checked_at' => now()->subDays(2),
            'availability_checked_at' => now()->subDays(10),
        ]);

        $this->assertTrue($this->service->isDataStale($product));
    }

    public function test_days_since_price_check_calculates_correctly(): void
    {
        $product = AffiliateProduct::factory()->create([
            'price_checked_at' => now()->subDays(5),
        ]);

        $days = $this->service->daysSincePriceCheck($product);
        $this->assertEquals(5, $days);
    }

    public function test_days_since_availability_check_calculates_correctly(): void
    {
        $product = AffiliateProduct::factory()->create([
            'availability_checked_at' => now()->subDays(3),
        ]);

        $days = $this->service->daysSinceAvailabilityCheck($product);
        $this->assertEquals(3, $days);
    }

    public function test_days_since_check_returns_null_when_never_checked(): void
    {
        $product = AffiliateProduct::factory()->create([
            'price_checked_at' => null,
            'availability_checked_at' => null,
        ]);

        $this->assertNull($this->service->daysSincePriceCheck($product));
        $this->assertNull($this->service->daysSinceAvailabilityCheck($product));
    }
}
