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

class AffiliateModelsRelationshipsTest extends TestCase
{
    #[Test]
    public function affiliate_link_belongs_to_product_opportunity()
    {
        $tenant = Tenant::factory()->create();
        $application = Application::factory()->for($tenant)->create();
        $trend = Trend::factory()->for($application)->create();
        $opportunity = ProductOpportunity::factory()
            ->for($application)
            ->for($trend)
            ->create();

        $link = AffiliateLink::factory()->create(['product_opportunity_id' => $opportunity->id]);

        $this->assertInstanceOf(ProductOpportunity::class, $link->opportunity);
        $this->assertEquals($opportunity->id, $link->opportunity->id);
    }

    #[Test]
    public function affiliate_link_has_many_conversions()
    {
        $link = AffiliateLink::factory()->create();
        $conversions = AffiliateConversion::factory(3)->create(['affiliate_link_id' => $link->id]);

        $this->assertCount(3, $link->conversions);
        $this->assertTrue($link->conversions->contains($conversions->first()));
    }

    #[Test]
    public function affiliate_conversion_belongs_to_product_opportunity()
    {
        $tenant = Tenant::factory()->create();
        $application = Application::factory()->for($tenant)->create();
        $trend = Trend::factory()->for($application)->create();
        $opportunity = ProductOpportunity::factory()
            ->for($application)
            ->for($trend)
            ->create();

        $conversion = AffiliateConversion::factory()->create(['product_opportunity_id' => $opportunity->id]);

        $this->assertInstanceOf(ProductOpportunity::class, $conversion->productOpportunity);
        $this->assertEquals($opportunity->id, $conversion->productOpportunity->id);
    }

    #[Test]
    public function curation_decision_belongs_to_product_opportunity()
    {
        $tenant = Tenant::factory()->create();
        $application = Application::factory()->for($tenant)->create();
        $user = User::factory()->create();
        $trend = Trend::factory()->for($application)->create();
        $opportunity = ProductOpportunity::factory()
            ->for($application)
            ->for($trend)
            ->create();

        $decision = CurationDecision::factory()->create([
            'product_opportunity_id' => $opportunity->id,
            'application_id' => $application->id,
            'user_id' => $user->id,
        ]);

        $this->assertInstanceOf(ProductOpportunity::class, $decision->opportunity);
        $this->assertEquals($opportunity->id, $decision->opportunity->id);
    }

    #[Test]
    public function curation_decision_belongs_to_user()
    {
        $user = User::factory()->create();
        $decision = CurationDecision::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $decision->user);
        $this->assertEquals($user->id, $decision->user->id);
    }

    #[Test]
    public function curation_decision_belongs_to_application()
    {
        $tenant = Tenant::factory()->create();
        $application = Application::factory()->for($tenant)->create();

        $decision = CurationDecision::factory()->create(['application_id' => $application->id]);

        $this->assertInstanceOf(Application::class, $decision->application);
        $this->assertEquals($application->id, $decision->application->id);
    }

    #[Test]
    public function curation_decision_approved_status()
    {
        $decision = CurationDecision::factory()->create(['decision' => CurationDecision::DECISION_APPROVED]);

        $this->assertTrue($decision->isApproved());
        $this->assertFalse($decision->isRejected());
    }

    #[Test]
    public function curation_decision_rejected_status()
    {
        $decision = CurationDecision::factory()->create(['decision' => CurationDecision::DECISION_REJECTED]);

        $this->assertFalse($decision->isApproved());
        $this->assertTrue($decision->isRejected());
    }

    #[Test]
    public function trend_has_many_opportunities()
    {
        $tenant = Tenant::factory()->create();
        $application = Application::factory()->for($tenant)->create();
        $trend = Trend::factory()->for($application)->create();

        ProductOpportunity::factory(3)->for($application)->for($trend)->create();

        $this->assertCount(3, $trend->opportunities);
    }

    #[Test]
    public function affiliate_product_has_many_opportunities()
    {
        $tenant = Tenant::factory()->create();
        $application = Application::factory()->for($tenant)->create();
        $product = AffiliateProduct::factory()->for($application)->create();
        $trend = Trend::factory()->for($application)->create();

        ProductOpportunity::factory(2)
            ->for($application)
            ->for($trend)
            ->for($product, 'affiliateProduct')
            ->create();

        $this->assertCount(2, $product->opportunities);
    }

    #[Test]
    public function affiliate_link_fillable_includes_product_opportunity_id()
    {
        $this->assertContains('product_opportunity_id', (new AffiliateLink())->getFillable());
    }

    #[Test]
    public function affiliate_conversion_fillable_includes_new_sprint_a_fields()
    {
        $fillable = (new AffiliateConversion())->getFillable();
        $this->assertContains('provider', $fillable);
        $this->assertContains('external_conversion_id', $fillable);
        $this->assertContains('product_opportunity_id', $fillable);
    }
}
