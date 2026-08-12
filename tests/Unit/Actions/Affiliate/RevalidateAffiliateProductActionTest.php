<?php

namespace Tests\Unit\Actions\Affiliate;

use App\Actions\Affiliate\RevalidateAffiliateProductAction;
use App\Models\AffiliateProduct;
use Tests\TestCase;

class RevalidateAffiliateProductActionTest extends TestCase
{
    public function test_revalidate_updates_timestamps(): void
    {
        $product = AffiliateProduct::factory()->create([
            'price_checked_at' => null,
            'availability_checked_at' => null,
        ]);

        $action = new RevalidateAffiliateProductAction();

        $updated = $action->execute($product);

        $this->assertNotNull($updated->price_checked_at);
        $this->assertNotNull($updated->availability_checked_at);
        $this->assertTrue($updated->price_checked_at->isToday());
        $this->assertTrue($updated->availability_checked_at->isToday());
    }

    public function test_revalidate_overwrites_old_timestamps(): void
    {
        $oldTime = now()->subDays(10);
        $product = AffiliateProduct::factory()->create([
            'price_checked_at' => $oldTime,
            'availability_checked_at' => $oldTime,
        ]);

        $action = new RevalidateAffiliateProductAction();

        $updated = $action->execute($product);

        $this->assertTrue($updated->price_checked_at->isToday());
        $this->assertTrue($updated->availability_checked_at->isToday());
        $this->assertNotEquals($oldTime, $updated->price_checked_at);
    }

    public function test_revalidate_returns_updated_product(): void
    {
        $product = AffiliateProduct::factory()->create([
            'price_checked_at' => null,
        ]);

        $action = new RevalidateAffiliateProductAction();

        $result = $action->execute($product);

        $this->assertInstanceOf(AffiliateProduct::class, $result);
        $this->assertEquals($product->id, $result->id);
    }
}
