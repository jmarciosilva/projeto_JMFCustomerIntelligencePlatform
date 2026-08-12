<?php

namespace Tests\Unit\Enums;

use App\Enums\MatchStatus;
use Tests\TestCase;

class MatchStatusTest extends TestCase
{
    public function test_match_status_has_correct_values(): void
    {
        $this->assertEquals('matched', MatchStatus::MATCHED->value);
        $this->assertEquals('no_match_in_catalog', MatchStatus::NO_MATCH_IN_CATALOG->value);
        $this->assertEquals('no_trend_data', MatchStatus::NO_TREND_DATA->value);
    }

    public function test_match_status_has_labels(): void
    {
        $this->assertEquals('Produto encontrado no catálogo', MatchStatus::MATCHED->label());
        $this->assertEquals('Nenhum produto correspondente no catálogo', MatchStatus::NO_MATCH_IN_CATALOG->label());
        $this->assertEquals('Sem dados de tendência', MatchStatus::NO_TREND_DATA->label());
    }

    public function test_match_status_has_colors(): void
    {
        $this->assertEquals('green', MatchStatus::MATCHED->color());
        $this->assertEquals('amber', MatchStatus::NO_MATCH_IN_CATALOG->color());
        $this->assertEquals('gray', MatchStatus::NO_TREND_DATA->color());
    }

    public function test_match_status_has_icons(): void
    {
        $this->assertEquals('✓', MatchStatus::MATCHED->icon());
        $this->assertEquals('⚠', MatchStatus::NO_MATCH_IN_CATALOG->icon());
        $this->assertEquals('○', MatchStatus::NO_TREND_DATA->icon());
    }
}
