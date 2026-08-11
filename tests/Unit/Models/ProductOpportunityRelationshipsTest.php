<?php

namespace Tests\Unit\Models;

use App\Models\AffiliateConversion;
use App\Models\AffiliateLink;
use App\Models\AffiliateProduct;
use App\Models\Application;
use App\Models\CurationDecision;
use App\Models\ProductOpportunity;
use App\Models\Tenant;
use App\Models\Trend;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProductOpportunityRelationshipsTest extends TestCase
{
    #[Test]
    public function it_belongs_to_trend()
    {
        $tenant = Tenant::factory()->create();
        $application = Application::factory()->for($tenant)->create();
        $trend = Trend::factory()->for($application)->create();
        $opportunity = ProductOpportunity::factory()
            ->for($application)
            ->for($trend)
            ->create();

        $this->assertInstanceOf(Trend::class, $opportunity->trend);
        $this->assertEquals($trend->id, $opportunity->trend->id);
    }

    #[Test]
    public function it_belongs_to_affiliate_product()
    {
        $tenant = Tenant::factory()->create();
        $application = Application::factory()->for($tenant)->create();
        $trend = Trend::factory()->for($application)->create();
        $product = AffiliateProduct::factory()->for($application)->create();
        $opportunity = ProductOpportunity::factory()
            ->for($application)
            ->for($trend)
            ->for($product, 'affiliateProduct')
            ->create();

        $this->assertInstanceOf(AffiliateProduct::class, $opportunity->affiliateProduct);
        $this->assertEquals($product->id, $opportunity->affiliateProduct->id);
    }

    #[Test]
    public function it_has_many_affiliate_links()
    {
        $tenant = Tenant::factory()->create();
        $application = Application::factory()->for($tenant)->create();
        $trend = Trend::factory()->for($application)->create();
        $opportunity = ProductOpportunity::factory()
            ->for($application)
            ->for($trend)
            ->create();

        $links = AffiliateLink::factory(3)->create();
        $links->each(fn ($link) => $link->update(['product_opportunity_id' => $opportunity->id]));

        $this->assertCount(3, $opportunity->affiliateLinks);
        $this->assertTrue($opportunity->affiliateLinks->contains($links->first()));
    }

    #[Test]
    public function it_has_many_affiliate_conversions()
    {
        $tenant = Tenant::factory()->create();
        $application = Application::factory()->for($tenant)->create();
        $trend = Trend::factory()->for($application)->create();
        $opportunity = ProductOpportunity::factory()
            ->for($application)
            ->for($trend)
            ->create();

        $conversions = AffiliateConversion::factory(2)->create();
        $conversions->each(fn ($conversion) => $conversion->update(['product_opportunity_id' => $opportunity->id]));

        $this->assertCount(2, $opportunity->affiliateConversions);
        $this->assertTrue($opportunity->affiliateConversions->contains($conversions->first()));
    }

    #[Test]
    public function it_has_many_curation_decisions()
    {
        $tenant = Tenant::factory()->create();
        $application = Application::factory()->for($tenant)->create();
        $user = User::factory()->create();
        $trend = Trend::factory()->for($application)->create();
        $opportunity = ProductOpportunity::factory()
            ->for($application)
            ->for($trend)
            ->create();

        CurationDecision::factory(2)->create([
            'product_opportunity_id' => $opportunity->id,
            'application_id' => $application->id,
            'user_id' => $user->id,
        ]);

        $this->assertCount(2, $opportunity->curationDecisions);
    }

    #[Test]
    public function it_has_scope_by_application()
    {
        $tenant1 = Tenant::factory()->create();
        $tenant2 = Tenant::factory()->create();
        $app1 = Application::factory()->for($tenant1)->create();
        $app2 = Application::factory()->for($tenant2)->create();
        $trend = Trend::factory()->for($app1)->create();

        ProductOpportunity::factory(3)->for($app1)->for($trend)->create();
        ProductOpportunity::factory(2)->for($app2)->create();

        $this->assertCount(3, ProductOpportunity::byApplication($app1->id)->get());
        $this->assertCount(2, ProductOpportunity::byApplication($app2->id)->get());
    }

    #[Test]
    public function it_has_scope_discovered()
    {
        $tenant = Tenant::factory()->create();
        $application = Application::factory()->for($tenant)->create();
        $trend = Trend::factory()->for($application)->create();

        ProductOpportunity::factory(2)->for($application)->for($trend)->create(['status_sprint_a' => 'DISCOVERED']);
        ProductOpportunity::factory(1)->for($application)->for($trend)->create(['status_sprint_a' => 'ANALYZING']);

        $this->assertCount(2, ProductOpportunity::discovered()->get());
    }

    #[Test]
    public function it_has_scope_approved()
    {
        $tenant = Tenant::factory()->create();
        $application = Application::factory()->for($tenant)->create();
        $trend = Trend::factory()->for($application)->create();

        ProductOpportunity::factory(1)->for($application)->for($trend)->create(['status_sprint_a' => 'APPROVED']);
        ProductOpportunity::factory(2)->for($application)->for($trend)->create(['status_sprint_a' => 'REJECTED']);

        $this->assertCount(1, ProductOpportunity::approved()->get());
    }

    #[Test]
    public function it_has_scope_recent_first()
    {
        $tenant = Tenant::factory()->create();
        $application = Application::factory()->for($tenant)->create();
        $trend = Trend::factory()->for($application)->create();

        $old = ProductOpportunity::factory()->for($application)->for($trend)->create(['created_at' => now()->subDays(10)]);
        $new = ProductOpportunity::factory()->for($application)->for($trend)->create(['created_at' => now()]);

        $results = ProductOpportunity::recentFirst()->get();
        $this->assertEquals($new->id, $results->first()->id);
        $this->assertEquals($old->id, $results->last()->id);
    }
}
