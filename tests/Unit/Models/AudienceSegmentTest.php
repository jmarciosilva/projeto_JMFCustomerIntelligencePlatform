<?php

namespace Tests\Unit\Models;

use App\Models\AudienceSegment;
use App\Models\ProductOpportunity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AudienceSegmentTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function create_audience_segment(): void
    {
        $segment = AudienceSegment::create([
            'name' => 'Profissionais',
            'description' => 'Público profissional com poder de compra',
            'age_range_approx' => '25-55',
            'interests' => ['tecnologia', 'desenvolvimento pessoal'],
            'purchase_intent_preference' => 'HIGH',
            'active' => true,
        ]);

        $this->assertDatabaseHas('audience_segments', [
            'id' => $segment->id,
            'name' => 'Profissionais',
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function scope_active(): void
    {
        AudienceSegment::create([
            'name' => 'Active',
            'age_range_approx' => '18-35',
            'active' => true,
        ]);
        AudienceSegment::create([
            'name' => 'Inactive',
            'age_range_approx' => '18-35',
            'active' => false,
        ]);

        $this->assertCount(1, AudienceSegment::active()->get());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function scope_inactive(): void
    {
        AudienceSegment::create([
            'name' => 'Active',
            'age_range_approx' => '18-35',
            'active' => true,
        ]);
        AudienceSegment::create([
            'name' => 'Inactive',
            'age_range_approx' => '18-35',
            'active' => false,
        ]);

        $this->assertCount(1, AudienceSegment::inactive()->get());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function has_many_opportunities(): void
    {
        $segment = AudienceSegment::create([
            'name' => 'Profissionais',
            'age_range_approx' => '25-55',
        ]);

        $opportunity = ProductOpportunity::factory()->create();

        $segment->opportunities()->attach($opportunity->id);

        $this->assertTrue($segment->opportunities()->where('id', $opportunity->id)->exists());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function interests_cast_to_array(): void
    {
        $segment = AudienceSegment::create([
            'name' => 'Tech',
            'age_range_approx' => '18-45',
            'interests' => ['tecnologia', 'inovação'],
        ]);

        $reloaded = AudienceSegment::find($segment->id);
        $this->assertIsArray($reloaded->interests);
        $this->assertContains('tecnologia', $reloaded->interests);
    }
}
