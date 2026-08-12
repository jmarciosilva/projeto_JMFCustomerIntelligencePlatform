<?php

namespace Tests\Unit\Domain\Affiliate;

use App\Domain\Affiliate\PerformanceScoreCalculator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PerformanceScoreCalculatorB1Test extends TestCase
{
    protected PerformanceScoreCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new PerformanceScoreCalculator;
    }

    #[Test]
    public function recurrency_factor_calculated_correctly()
    {
        // Recurrency = 10 conversions / 90 days = ~11.1% (normalized 0-100)
        $result = $this->calculator->calculate(
            clicks: 100,
            conversions: 10,
            impressions: 1000,
            recurrency_rate: 11.1
        );

        $this->assertNotNull($result['score']);
        $this->assertEquals(11.1, $result['factors']['recurrency']);
        $this->assertStringContainsString('Rec:', $result['breakdown']['calculation']);
    }

    #[Test]
    public function recurrency_null_treated_as_zero()
    {
        // Sem dados de recurrency (Sprint A compatibility)
        $result = $this->calculator->calculate(
            clicks: 100,
            conversions: 10,
            impressions: 1000,
            recurrency_rate: null
        );

        $this->assertNull($result['factors']['recurrency']);
        $this->assertNotNull($result['score']);
        // Score calcula com CTR e ConversionRate, mas não recurrency
    }

    #[Test]
    public function recurrency_capped_at_100()
    {
        // Recurrency não pode ser > 100
        $result = $this->calculator->calculate(
            clicks: 100,
            conversions: 10,
            impressions: 1000,
            recurrency_rate: 150.0
        );

        $this->assertEquals(100, $result['factors']['recurrency']);
    }

    #[Test]
    public function high_confidence_with_three_factors()
    {
        // Com 3 fatores presentes: CTR + ConversionRate + Recurrency
        // Total weights = 40 + 40 + 20 = 100 (HIGH confidence)
        $result = $this->calculator->calculate(
            clicks: 100,
            conversions: 10,
            impressions: 1000,
            recurrency_rate: 15.0
        );

        $this->assertEquals('HIGH', $result['confidence']);
    }

    #[Test]
    public function medium_confidence_with_two_factors()
    {
        // Com 2 fatores: CTR + ConversionRate (recurrency = null)
        // Total weights = 40 + 40 = 80 (MEDIUM confidence, não HIGH)
        $result = $this->calculator->calculate(
            clicks: 100,
            conversions: 10,
            impressions: 1000,
            recurrency_rate: null
        );

        $this->assertEquals('MEDIUM', $result['confidence']);
    }

    #[Test]
    public function low_confidence_with_one_factor()
    {
        // Com 1 fator: apenas ConversionRate (sem impressions, sem recurrency)
        $result = $this->calculator->calculate(
            clicks: 100,
            conversions: 10,
            impressions: null,
            recurrency_rate: null
        );

        $this->assertEquals('LOW', $result['confidence']);
    }

    #[Test]
    public function score_weights_three_factors()
    {
        // Score = CTR × 0.40 + ConversionRate × 0.40 + Recurrency × 0.20
        // CTR = 10%, ConversionRate = 10%, Recurrency = 10%
        // Score = (10 × 0.40) + (10 × 0.40) + (10 × 0.20) = 4 + 4 + 2 = 10
        $result = $this->calculator->calculate(
            clicks: 100,
            conversions: 10,
            impressions: 1000,
            recurrency_rate: 10.0
        );

        // Verificar que score inclui todas as 3 componentes
        $this->assertNotNull($result['score']);
        $this->assertStringContainsString('CTR:', $result['breakdown']['calculation']);
        $this->assertStringContainsString('Conv:', $result['breakdown']['calculation']);
        $this->assertStringContainsString('Rec:', $result['breakdown']['calculation']);
    }

    #[Test]
    public function backwards_compatible_sprint_a()
    {
        // Sprint A: recurrency_rate não é passado (defaults null)
        $result = $this->calculator->calculate(
            clicks: 100,
            conversions: 5,
            impressions: 500
            // recurrency_rate omitido = null
        );

        // Deve funcionar como antes (sem HIGH confidence)
        $this->assertEquals('MEDIUM', $result['confidence']);
        $this->assertNull($result['factors']['recurrency']);
    }

    #[Test]
    public function recurrency_zero_is_valid()
    {
        // Recurrency = 0 é válido (contato não tem histórico de compra)
        $result = $this->calculator->calculate(
            clicks: 100,
            conversions: 5,
            impressions: 500,
            recurrency_rate: 0.0
        );

        $this->assertEquals(0.0, $result['factors']['recurrency']);
        $this->assertEquals('HIGH', $result['confidence']);
        $this->assertNotNull($result['score']);
    }

    #[Test]
    public function recurrency_negative_treated_as_null()
    {
        // Valores negativos são tratados como dados não disponíveis
        $result = $this->calculator->calculate(
            clicks: 100,
            conversions: 5,
            impressions: 500,
            recurrency_rate: -1.0
        );

        $this->assertNull($result['factors']['recurrency']);
        $this->assertEquals('MEDIUM', $result['confidence']);
    }

    #[Test]
    public function all_three_factors_null_insufficient_data()
    {
        // Nenhum fator disponível
        $result = $this->calculator->calculate(
            clicks: null,
            conversions: null,
            impressions: null,
            recurrency_rate: null
        );

        $this->assertNull($result['score']);
        $this->assertEquals('INSUFFICIENT_DATA', $result['confidence']);
    }

    #[Test]
    public function breakdown_includes_all_factors_sprint_b()
    {
        $result = $this->calculator->calculate(
            clicks: 100,
            conversions: 10,
            impressions: 1000,
            recurrency_rate: 8.5
        );

        $breakdown = $result['breakdown'];
        $this->assertArrayHasKey('ctr', $breakdown);
        $this->assertArrayHasKey('conversion_rate', $breakdown);
        $this->assertArrayHasKey('recurrency', $breakdown);
        $this->assertArrayHasKey('ctr_normalized_weight', $breakdown);
        $this->assertArrayHasKey('conversion_rate_normalized_weight', $breakdown);
        $this->assertArrayHasKey('recurrency_normalized_weight', $breakdown);
    }
}
