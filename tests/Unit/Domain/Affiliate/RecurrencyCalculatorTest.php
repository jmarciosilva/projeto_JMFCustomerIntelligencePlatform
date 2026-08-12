<?php

namespace Tests\Unit\Domain\Affiliate;

use App\Domain\Affiliate\RecurrencyCalculator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RecurrencyCalculatorTest extends TestCase
{
    protected RecurrencyCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new RecurrencyCalculator;
    }

    #[Test]
    public function normalize_recurrency_basic_calculation()
    {
        // 9 conversões / 90 dias = 10%
        $rate = $this->calculator->normalizeRecurrency(9);
        $this->assertEquals(10.0, $rate);
    }

    #[Test]
    public function normalize_recurrency_single_conversion()
    {
        // 1 conversão / 90 dias = 1.11%
        $rate = $this->calculator->normalizeRecurrency(1);
        $this->assertEqualsWithDelta(1.11, $rate, 0.01);
    }

    #[Test]
    public function normalize_recurrency_many_conversions()
    {
        // 45 conversões / 90 dias = 50%
        $rate = $this->calculator->normalizeRecurrency(45);
        $this->assertEquals(50.0, $rate);
    }

    #[Test]
    public function normalize_recurrency_capped_at_100()
    {
        // 100+ conversões / 90 dias = 100% (capped)
        $rate = $this->calculator->normalizeRecurrency(100);
        $this->assertEquals(100.0, $rate);

        $rate2 = $this->calculator->normalizeRecurrency(200);
        $this->assertEquals(100.0, $rate2);
    }

    #[Test]
    public function normalize_recurrency_zero()
    {
        // 0 conversões = 0%
        $rate = $this->calculator->normalizeRecurrency(0);
        $this->assertEquals(0.0, $rate);
    }

    #[Test]
    public function normalize_for_period_different_duration()
    {
        // 30 conversões / 30 dias = 100%
        $rate = $this->calculator->normalizeForPeriod(30, 30);
        $this->assertEquals(100.0, $rate);

        // 30 conversões / 60 dias = 50%
        $rate2 = $this->calculator->normalizeForPeriod(30, 60);
        $this->assertEquals(50.0, $rate2);
    }

    #[Test]
    public function normalize_for_period_zero_days()
    {
        // Dias <= 0 retorna 0
        $rate = $this->calculator->normalizeForPeriod(10, 0);
        $this->assertEquals(0.0, $rate);

        $rate2 = $this->calculator->normalizeForPeriod(10, -5);
        $this->assertEquals(0.0, $rate2);
    }

    #[Test]
    public function normalize_for_period_zero_conversions()
    {
        // Conversões = 0 retorna 0
        $rate = $this->calculator->normalizeForPeriod(0, 90);
        $this->assertEquals(0.0, $rate);
    }

    #[Test]
    public function decimal_precision_preserved()
    {
        // 8 conversões / 90 dias = 8.888...%
        $rate = $this->calculator->normalizeRecurrency(8);
        $this->assertEqualsWithDelta(8.89, $rate, 0.01);

        // Verificar que valor é float
        $this->assertIsFloat($rate);
    }
}
