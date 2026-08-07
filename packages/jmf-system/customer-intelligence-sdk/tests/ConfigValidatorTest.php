<?php

namespace JmfSystem\CustomerIntelligence\Tests;

use JmfSystem\CustomerIntelligence\ConfigValidator;
use Orchestra\Testbench\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ConfigValidatorTest extends TestCase
{
    protected function defineEnvironment($app)
    {
        $app['config']->set('customer-intelligence.validate_on_boot', false);
    }

    protected function getPackageProviders($app)
    {
        return ['JmfSystem\\CustomerIntelligence\\CustomerIntelligenceServiceProvider'];
    }

    #[Test]
    public function valida_configuracao_completa_sem_erros(): void
    {
        config([
            'customer-intelligence.enabled' => true,
            'customer-intelligence.base_url' => 'https://ci.example.com/api/v1',
            'customer-intelligence.token' => 'test-token-123',
            'customer-intelligence.timeout' => 5,
            'customer-intelligence.tries' => 3,
        ]);

        $errors = ConfigValidator::getValidationErrors();

        $this->assertEmpty($errors);
    }

    #[Test]
    public function detecta_base_url_faltando(): void
    {
        config([
            'customer-intelligence.enabled' => true,
            'customer-intelligence.base_url' => null,
            'customer-intelligence.token' => 'test-token',
            'customer-intelligence.timeout' => 5,
            'customer-intelligence.tries' => 3,
        ]);

        $errors = ConfigValidator::getValidationErrors();

        $this->assertContains('JMF_CI_BASE_URL não está configurada', $errors);
    }

    #[Test]
    public function detecta_token_faltando(): void
    {
        config([
            'customer-intelligence.enabled' => true,
            'customer-intelligence.base_url' => 'https://ci.example.com/api/v1',
            'customer-intelligence.token' => null,
            'customer-intelligence.timeout' => 5,
            'customer-intelligence.tries' => 3,
        ]);

        $errors = ConfigValidator::getValidationErrors();

        $this->assertContains('JMF_CI_TOKEN não está configurado', $errors);
    }

    #[Test]
    public function detecta_base_url_invalida(): void
    {
        config([
            'customer-intelligence.enabled' => true,
            'customer-intelligence.base_url' => 'not-a-valid-url',
            'customer-intelligence.token' => 'test-token',
            'customer-intelligence.timeout' => 5,
            'customer-intelligence.tries' => 3,
        ]);

        $errors = ConfigValidator::getValidationErrors();

        $this->assertContains('JMF_CI_BASE_URL não é uma URL válida', $errors);
    }

    #[Test]
    public function detecta_timeout_invalido(): void
    {
        config([
            'customer-intelligence.enabled' => true,
            'customer-intelligence.base_url' => 'https://ci.example.com/api/v1',
            'customer-intelligence.token' => 'test-token',
            'customer-intelligence.timeout' => 0,
            'customer-intelligence.tries' => 3,
        ]);

        $errors = ConfigValidator::getValidationErrors();

        $this->assertContains('JMF_CI_TIMEOUT deve ser >= 1 segundo', $errors);
    }

    #[Test]
    public function detecta_tries_invalido(): void
    {
        config([
            'customer-intelligence.enabled' => true,
            'customer-intelligence.base_url' => 'https://ci.example.com/api/v1',
            'customer-intelligence.token' => 'test-token',
            'customer-intelligence.timeout' => 5,
            'customer-intelligence.tries' => 0,
        ]);

        $errors = ConfigValidator::getValidationErrors();

        $this->assertContains('JMF_CI_TRIES deve ser >= 1', $errors);
    }

    #[Test]
    public function nao_valida_se_sdk_esta_desabilitado(): void
    {
        config([
            'customer-intelligence.enabled' => false,
            'customer-intelligence.base_url' => null,
            'customer-intelligence.token' => null,
        ]);

        $errors = ConfigValidator::getValidationErrors();

        $this->assertEmpty($errors);
    }

    #[Test]
    public function lanca_excecao_em_validacao_com_erros(): void
    {
        config([
            'customer-intelligence.enabled' => true,
            'customer-intelligence.base_url' => null,
            'customer-intelligence.token' => null,
            'customer-intelligence.timeout' => 5,
            'customer-intelligence.tries' => 3,
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/Configuração inválida/');

        ConfigValidator::validate();
    }

    #[Test]
    public function nao_lanca_excecao_se_configuracao_valida(): void
    {
        config([
            'customer-intelligence.enabled' => true,
            'customer-intelligence.base_url' => 'https://ci.example.com/api/v1',
            'customer-intelligence.token' => 'test-token',
            'customer-intelligence.timeout' => 5,
            'customer-intelligence.tries' => 3,
        ]);

        ConfigValidator::validate();

        $this->assertTrue(true);
    }

    #[Test]
    public function retorna_status_da_configuracao(): void
    {
        config([
            'customer-intelligence.enabled' => true,
            'customer-intelligence.base_url' => 'https://ci.example.com/api/v1',
            'customer-intelligence.token' => 'test-token-123',
            'customer-intelligence.timeout' => 5,
            'customer-intelligence.tries' => 3,
            'customer-intelligence.sync' => false,
        ]);

        $status = ConfigValidator::status();

        $this->assertTrue($status['enabled']);
        $this->assertEquals('✓', $status['base_url']);
        $this->assertStringContainsString('✓', $status['token']);
        $this->assertEquals('5s', $status['timeout']);
        $this->assertEquals(3, $status['tries']);
        $this->assertFalse($status['sync']);
        $this->assertEmpty($status['errors']);
    }
}
