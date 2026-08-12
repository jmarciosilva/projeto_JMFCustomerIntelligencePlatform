<?php

namespace Tests\Unit\Models;

use App\Enums\MatchStatus;
use App\Models\TrendProductMatch;
use Tests\TestCase;

class TrendProductMatchStatusTest extends TestCase
{
    public function test_match_status_defaults_to_matched(): void
    {
        $match = TrendProductMatch::factory()->create();

        $this->assertEquals(MatchStatus::MATCHED, $match->match_status);
    }

    public function test_match_status_can_be_set_to_no_match_in_catalog(): void
    {
        $match = TrendProductMatch::factory()->create([
            'match_status' => MatchStatus::NO_MATCH_IN_CATALOG,
        ]);

        $this->assertEquals(MatchStatus::NO_MATCH_IN_CATALOG, $match->match_status);
    }

    public function test_match_status_can_be_set_to_no_trend_data(): void
    {
        $match = TrendProductMatch::factory()->create([
            'match_status' => MatchStatus::NO_TREND_DATA,
        ]);

        $this->assertEquals(MatchStatus::NO_TREND_DATA, $match->match_status);
    }

    public function test_match_status_is_cast_to_enum(): void
    {
        $match = TrendProductMatch::factory()->create([
            'match_status' => 'matched',
        ]);

        $this->assertInstanceOf(MatchStatus::class, $match->match_status);
    }

    public function test_match_status_can_be_updated(): void
    {
        $match = TrendProductMatch::factory()->create([
            'match_status' => MatchStatus::MATCHED,
        ]);

        $match->update(['match_status' => MatchStatus::NO_MATCH_IN_CATALOG]);

        $this->assertEquals(MatchStatus::NO_MATCH_IN_CATALOG, $match->refresh()->match_status);
    }
}
