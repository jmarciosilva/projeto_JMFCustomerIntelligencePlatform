<?php

namespace Tests\Unit\Models;

use App\Models\AffiliateProduct;
use Tests\TestCase;

class AffiliateProductDataFreshnessTest extends TestCase
{
    public function test_with_stale_data_scope_finds_unchecked_products(): void
    {
        $stale = AffiliateProduct::factory()->create([
            'price_checked_at' => null,
            'availability_checked_at' => null,
        ]);

        $fresh = AffiliateProduct::factory()->create([
            'price_checked_at' => now()->subDays(2),
            'availability_checked_at' => now()->subDays(2),
        ]);

        $staleProducts = AffiliateProduct::withStaleData(7)->get();

        $this->assertTrue($staleProducts->contains('id', $stale->id));
        $this->assertFalse($staleProducts->contains('id', $fresh->id));
    }

    public function test_with_stale_data_scope_finds_old_price_checks(): void
    {
        $stale = AffiliateProduct::factory()->create([
            'price_checked_at' => now()->subDays(10),
            'availability_checked_at' => now()->subDays(2),
        ]);

        $fresh = AffiliateProduct::factory()->create([
            'price_checked_at' => now()->subDays(2),
            'availability_checked_at' => now()->subDays(2),
        ]);

        $staleProducts = AffiliateProduct::withStaleData(7)->get();

        $this->assertTrue($staleProducts->contains('id', $stale->id));
        $this->assertFalse($staleProducts->contains('id', $fresh->id));
    }

    public function test_with_stale_data_scope_finds_old_availability_checks(): void
    {
        $stale = AffiliateProduct::factory()->create([
            'price_checked_at' => now()->subDays(2),
            'availability_checked_at' => now()->subDays(10),
        ]);

        $fresh = AffiliateProduct::factory()->create([
            'price_checked_at' => now()->subDays(2),
            'availability_checked_at' => now()->subDays(2),
        ]);

        $staleProducts = AffiliateProduct::withStaleData(7)->get();

        $this->assertTrue($staleProducts->contains('id', $stale->id));
        $this->assertFalse($staleProducts->contains('id', $fresh->id));
    }

    public function test_with_fresh_data_scope_requires_both_checks_fresh(): void
    {
        $fresh = AffiliateProduct::factory()->create([
            'price_checked_at' => now()->subDays(2),
            'availability_checked_at' => now()->subDays(2),
        ]);

        $onlyPriceFresh = AffiliateProduct::factory()->create([
            'price_checked_at' => now()->subDays(2),
            'availability_checked_at' => now()->subDays(10),
        ]);

        $freshProducts = AffiliateProduct::withFreshData(7)->get();

        $this->assertTrue($freshProducts->contains('id', $fresh->id));
        $this->assertFalse($freshProducts->contains('id', $onlyPriceFresh->id));
    }

    public function test_with_fresh_data_scope_respects_threshold(): void
    {
        $fresh = AffiliateProduct::factory()->create([
            'price_checked_at' => now()->subDays(3),
            'availability_checked_at' => now()->subDays(3),
        ]);

        $stale = AffiliateProduct::factory()->create([
            'price_checked_at' => now()->subDays(10),
            'availability_checked_at' => now()->subDays(10),
        ]);

        $freshProducts = AffiliateProduct::withFreshData(7)->get();

        $this->assertTrue($freshProducts->contains('id', $fresh->id));
        $this->assertFalse($freshProducts->contains('id', $stale->id));
    }
}
