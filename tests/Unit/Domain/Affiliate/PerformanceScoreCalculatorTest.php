<?php

namespace Tests\Unit\Domain\Affiliate;

use App\Domain\Affiliate\PerformanceScoreCalculator;
use PHPUnit\Framework\TestCase;

class PerformanceScoreCalculatorTest extends TestCase
{
    private PerformanceScoreCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new PerformanceScoreCalculator();
    }

    // Sprint A: Dois Fatores (CTR + ConversionRate)
    public function test_both_ctr_and_conversion_rate_available_medium_confidence(): void
    {
        $score = $this->calculator->calculate(
            clicks: 100,
            conversions: 10,
            impressions: 1000
        );

        $this->assertGreaterThan(0, $score['score']);
        // Com 80% de pesos disponíveis (40 CTR + 40 Conv), confidence é MEDIUM
        $this->assertEquals('MEDIUM', $score['confidence']);
        $this->assertNotNull($score['breakdown']['ctr']);
        $this->assertNotNull($score['breakdown']['conversion_rate']);
        $this->assertNull($score['breakdown']['recurrency']); // ← Sempre null em Sprint A
    }

    // Only CTR available
    public function test_only_ctr_available_low_confidence(): void
    {
        $score = $this->calculator->calculate(
            clicks: 100,
            conversions: null, // ← null
            impressions: 1000
        );

        $this->assertNotNull($score['breakdown']['ctr']);
        $this->assertNull($score['breakdown']['conversion_rate']);
        $this->assertNull($score['breakdown']['recurrency']);
        // Com apenas CTR (40% do peso original), confidence é LOW
        $this->assertEquals('LOW', $score['confidence']);
        // Score deve refletir único CTR disponível
        $expected_ctr = (100 / 1000) * 100; // = 10
        $this->assertEquals($expected_ctr, $score['breakdown']['ctr'], 1);
    }

    // Only Conversion Rate available
    public function test_only_conversion_rate_available_low_confidence(): void
    {
        $score = $this->calculator->calculate(
            clicks: 100,
            conversions: 10,
            impressions: null // ← null
        );

        $this->assertNull($score['breakdown']['ctr']);
        $this->assertNotNull($score['breakdown']['conversion_rate']);
        $this->assertNull($score['breakdown']['recurrency']);
        // Com apenas ConversionRate (40% do peso original), confidence é LOW
        $this->assertEquals('LOW', $score['confidence']);
        // ConversionRate recebe 100% do peso disponível
        $expected_conv = (10 / 100) * 100; // = 10%
        $this->assertEquals($expected_conv, $score['breakdown']['conversion_rate'], 1);
    }

    // No factors available
    public function test_no_factors_available_insufficient_data(): void
    {
        $score = $this->calculator->calculate(
            clicks: null,
            conversions: null,
            impressions: null
        );

        $this->assertNull($score['score']);
        $this->assertEquals('INSUFFICIENT_DATA', $score['confidence']);
        $this->assertNull($score['breakdown']['ctr']);
        $this->assertNull($score['breakdown']['conversion_rate']);
        $this->assertNull($score['breakdown']['recurrency']);
    }

    // Score bounds
    public function test_score_always_between_0_and_100(): void
    {
        $high_score = $this->calculator->calculate(
            clicks: 50,
            conversions: 25,
            impressions: 100
        );

        $this->assertGreaterThanOrEqual(0, $high_score['score']);
        $this->assertLessThanOrEqual(100, $high_score['score']);
    }

    // Recurrency always null in Sprint A
    public function test_recurrency_always_null_in_sprint_a(): void
    {
        $score = $this->calculator->calculate(
            clicks: 100,
            conversions: 10,
            impressions: 1000
        );

        $this->assertNull($score['breakdown']['recurrency']);
    }

    // Confidence levels for Sprint A
    public function test_confidence_levels_correct_for_sprint_a(): void
    {
        // Nenhum fator: INSUFFICIENT_DATA
        $no_data = $this->calculator->calculate(null, null, null);
        $this->assertEquals('INSUFFICIENT_DATA', $no_data['confidence']);

        // Um fator (40% de peso): LOW
        $one_factor = $this->calculator->calculate(100, null, 1000); // só CTR
        $this->assertEquals('LOW', $one_factor['confidence']);

        // Dois fatores (80% de peso): MEDIUM
        $two_factors = $this->calculator->calculate(100, 10, 1000);
        $this->assertEquals('MEDIUM', $two_factors['confidence']);
    }

    // Weight normalization
    public function test_weights_normalize_correctly_with_one_factor(): void
    {
        $score = $this->calculator->calculate(
            clicks: 100,
            conversions: null,
            impressions: 1000
        );

        // Com apenas CTR (40% original), recebe 100% do peso disponível
        $this->assertNotNull($score['breakdown']['ctr_normalized_weight']);
        $this->assertGreaterThan(0, $score['breakdown']['ctr_normalized_weight']);
    }

    // High performance
    public function test_high_ctr_and_conversion_rate(): void
    {
        $score = $this->calculator->calculate(
            clicks: 100,
            conversions: 50,
            impressions: 1000
        );

        // CTR = 10%, ConversionRate = 50%
        // Score ≈ (10 + 50) / 2 = 30 (média ponderada)
        $this->assertGreaterThan(0, $score['score']);
        $this->assertLessThanOrEqual(100, $score['score']);
    }

    // Edge case: zero impressions
    public function test_zero_impressions_skips_ctr(): void
    {
        $score = $this->calculator->calculate(
            clicks: 0,
            conversions: 0,
            impressions: 0
        );

        $this->assertNull($score['breakdown']['ctr']);
        $this->assertNull($score['breakdown']['conversion_rate']);
        $this->assertEquals('INSUFFICIENT_DATA', $score['confidence']);
    }

    // Edge case: conversions without clicks
    public function test_conversions_without_clicks_only_ctr(): void
    {
        // Logicamente impossível ter conversões sem cliques
        // Mas tecnicamente CTR é válido (0%), ConversionRate não
        $score = $this->calculator->calculate(
            clicks: 0,
            conversions: 10,
            impressions: 1000
        );

        // CTR é disponível (0%), ConversionRate não
        $this->assertNotNull($score['breakdown']['ctr']);
        $this->assertNull($score['breakdown']['conversion_rate']);
        $this->assertEquals('LOW', $score['confidence']);
        $this->assertEquals(0, $score['score']); // CTR=0%
    }

    // Calculation explanation
    public function test_calculation_explains_result(): void
    {
        $score = $this->calculator->calculate(
            clicks: 100,
            conversions: 10,
            impressions: 1000
        );

        $this->assertNotEmpty($score['breakdown']['calculation']);
        $this->assertStringContainsString('CTR', $score['breakdown']['calculation']);
        $this->assertStringContainsString('Conv', $score['breakdown']['calculation']);
    }
}
