<?php

namespace Tests\Unit\Domain\Affiliate;

use App\Domain\Affiliate\AudienceTargetingService;
use App\Models\AudienceSegment;
use App\Models\ProductOpportunity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AudienceTargetingServiceTest extends TestCase
{
    use RefreshDatabase;

    private AudienceTargetingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AudienceTargetingService();
        $this->seedAudiences();
    }

    private function seedAudiences(): void
    {
        AudienceSegment::create([
            'name' => 'Profissionais',
            'description' => 'Profissionais com poder de compra',
            'age_range_approx' => '25-55',
            'interests' => ['tecnologia', 'desenvolvimento pessoal'],
            'purchase_intent_preference' => 'HIGH',
            'active' => true,
        ]);

        AudienceSegment::create([
            'name' => 'Jovens Adultos',
            'description' => 'Jovens de 18-35 anos',
            'age_range_approx' => '18-35',
            'interests' => ['moda', 'lifestyle'],
            'purchase_intent_preference' => 'MEDIUM',
            'active' => true,
        ]);

        AudienceSegment::create([
            'name' => 'Tecnologia Entusiastas',
            'description' => 'Adotadores precoces de tecnologia',
            'age_range_approx' => '18-45',
            'interests' => ['tecnologia', 'gadgets'],
            'purchase_intent_preference' => 'MEDIUM',
            'active' => true,
        ]);

        AudienceSegment::create([
            'name' => 'Curiosos & Browsing',
            'description' => 'Público ocasional',
            'age_range_approx' => '18-70',
            'interests' => ['exploração'],
            'purchase_intent_preference' => 'LOW',
            'active' => true,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function determine_audiences_for_high_intent_opportunity(): void
    {
        $opportunity = ProductOpportunity::factory()->create([
            'purchase_intent_label' => 'HIGH',
        ]);

        $audiences = $this->service->determineAudiencesForOpportunity($opportunity);

        $this->assertGreaterThan(0, count($audiences));
        $this->assertTrue($audiences->pluck('name')->contains('Profissionais'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function determine_audiences_for_medium_intent_opportunity(): void
    {
        $opportunity = ProductOpportunity::factory()->create([
            'purchase_intent_label' => 'MEDIUM',
        ]);

        $audiences = $this->service->determineAudiencesForOpportunity($opportunity);

        $this->assertGreaterThan(0, count($audiences));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function determine_audiences_for_low_intent_opportunity(): void
    {
        $opportunity = ProductOpportunity::factory()->create([
            'purchase_intent_label' => 'LOW',
        ]);

        $audiences = $this->service->determineAudiencesForOpportunity($opportunity);

        $this->assertGreaterThan(0, count($audiences));
        $this->assertTrue($audiences->pluck('name')->contains('Curiosos & Browsing'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function only_returns_active_audiences(): void
    {
        AudienceSegment::where('name', 'Profissionais')->first()->update(['active' => false]);

        $opportunity = ProductOpportunity::factory()->create([
            'purchase_intent_label' => 'HIGH',
        ]);

        $audiences = $this->service->determineAudiencesForOpportunity($opportunity);

        $this->assertFalse($audiences->pluck('name')->contains('Profissionais'));
    }
}
