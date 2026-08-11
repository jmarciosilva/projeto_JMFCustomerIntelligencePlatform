<?php

namespace Tests\Unit\Domain\Affiliate;

use App\Domain\Affiliate\PurchaseIntentClassifier;
use PHPUnit\Framework\TestCase;

class PurchaseIntentClassifierTest extends TestCase
{
    private PurchaseIntentClassifier $classifier;

    protected function setUp(): void
    {
        parent::setUp();
        $this->classifier = new PurchaseIntentClassifier();
    }

    // LOW (0-39) Tests
    public function test_pure_informational_returns_low(): void
    {
        $result = $this->classifier->classify('como funciona cafeteira');

        $this->assertLessThan(40, $result['score']);
        $this->assertEquals('LOW', $result['label']);
        $this->assertContains('como', $result['breakdown']['matched_signals']['INFORMATIONAL'] ?? []);
    }

    public function test_tutorial_keyword_returns_low(): void
    {
        $result = $this->classifier->classify('tutorial cafeteira expresso');

        $this->assertLessThan(40, $result['score']);
        $this->assertEquals('LOW', $result['label']);
    }

    // MEDIUM (40-69) Tests
    public function test_investigation_keyword_returns_medium(): void
    {
        $result = $this->classifier->classify('melhor cafeteira');

        $this->assertGreaterThanOrEqual(40, $result['score']);
        $this->assertLessThan(70, $result['score']);
        $this->assertEquals('MEDIUM', $result['label']);
        $this->assertContains('melhor', $result['breakdown']['matched_signals']['INVESTIGATION'] ?? []);
    }

    public function test_comparison_keyword_returns_medium(): void
    {
        $result = $this->classifier->classify('cafeteira vs air fryer');

        $this->assertGreaterThanOrEqual(40, $result['score']);
        $this->assertLessThan(70, $result['score']);
        $this->assertEquals('MEDIUM', $result['label']);
    }

    // HIGH (70-100) Tests
    public function test_transactional_keyword_returns_high(): void
    {
        $result = $this->classifier->classify('comprar cafeteira');

        $this->assertGreaterThanOrEqual(70, $result['score']);
        $this->assertEquals('HIGH', $result['label']);
        $this->assertContains('comprar', $result['breakdown']['matched_signals']['TRANSACTIONAL'] ?? []);
    }

    public function test_price_keyword_returns_high(): void
    {
        $result = $this->classifier->classify('preço cafeteira');

        $this->assertGreaterThanOrEqual(70, $result['score']);
        $this->assertEquals('HIGH', $result['label']);
    }

    public function test_promotion_keyword_returns_high(): void
    {
        $result = $this->classifier->classify('promoção cafeteira');

        $this->assertGreaterThanOrEqual(70, $result['score']);
        $this->assertEquals('HIGH', $result['label']);
    }

    // Multiple Signals Tests
    public function test_multiple_transactional_signals_boost_score(): void
    {
        $result1 = $this->classifier->classify('comprar cafeteira');
        $result2 = $this->classifier->classify('comprar cafeteira promoção');

        $this->assertGreaterThan($result1['score'], $result2['score']);
        $this->assertEquals('HIGH', $result2['label']);
        $this->assertGreaterThanOrEqual(85, $result2['score']);
    }

    public function test_multiple_signals_with_intensity(): void
    {
        $result = $this->classifier->classify('onde comprar cafeteira com desconto e cupom hoje');

        $this->assertEquals('HIGH', $result['label']);
        $this->assertGreaterThanOrEqual(90, $result['score']);
        $this->assertEquals(5, $result['breakdown']['adjustments']['intensity_bonus']);
    }

    // Score Bounds Tests
    public function test_score_never_exceeds_100(): void
    {
        $result = $this->classifier->classify('comprar comprar promoção promoção hoje hoje urgente');

        $this->assertLessThanOrEqual(100, $result['score']);
    }

    public function test_score_never_below_0(): void
    {
        $result = $this->classifier->classify('como funciona how works');

        $this->assertGreaterThanOrEqual(0, $result['score']);
    }

    // Breakdown Tests
    public function test_breakdown_explains_score(): void
    {
        $result = $this->classifier->classify('comprar cafeteira promoção');

        $this->assertIsArray($result['breakdown']);
        $this->assertNotEmpty($result['breakdown']['matched_signals']);
        $this->assertEquals($result['breakdown']['final_score'], $result['score']);
        $this->assertTrue(
            $result['breakdown']['adjustments']['multiple_signals_bonus'] > 0
        );
    }

    // Intensity Modifier Tests
    public function test_intensity_bonus_only_with_non_informational(): void
    {
        $result = $this->classifier->classify('como funciona cafeteira urgente');

        // "urgente" é modifier mas base_intent é INFORMATIONAL
        // Logo não deve receber intensity bonus
        $this->assertEquals(0, $result['breakdown']['adjustments']['intensity_bonus']);
    }

    public function test_intensity_bonus_with_transactional(): void
    {
        $result = $this->classifier->classify('comprar cafeteira agora');

        // "agora" é intensity modifier + base_intent é TRANSACTIONAL
        // Logo deve receber intensity bonus
        $this->assertEquals(5, $result['breakdown']['adjustments']['intensity_bonus']);
    }

    // Case Insensitivity Tests
    public function test_case_insensitive_matching(): void
    {
        $result_lower = $this->classifier->classify('comprar cafeteira');
        $result_upper = $this->classifier->classify('COMPRAR CAFETEIRA');

        $this->assertEquals($result_lower['score'], $result_upper['score']);
        $this->assertEquals($result_lower['label'], $result_upper['label']);
    }
}
