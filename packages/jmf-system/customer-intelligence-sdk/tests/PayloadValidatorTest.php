<?php

namespace JmfSystem\CustomerIntelligence\Tests;

use JmfSystem\CustomerIntelligence\PayloadValidator;
use Orchestra\Testbench\TestCase;

class PayloadValidatorTest extends TestCase
{
    protected function defineEnvironment($app)
    {
        $app['config']->set('customer-intelligence.validate_on_boot', false);
    }

    protected function getPackageProviders($app)
    {
        return ['JmfSystem\\CustomerIntelligence\\CustomerIntelligenceServiceProvider'];
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function valida_evento_valido_sem_erros(): void
    {
        $payload = [
            'event_id' => 'evt-123',
            'event_name' => 'product.viewed',
            'visitor_id' => 'vis-123',
            'occurred_at' => now()->toIso8601String(),
        ];

        PayloadValidator::validate('events', $payload);

        $this->assertTrue(true);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function valida_identify_valido_sem_erros(): void
    {
        $payload = [
            'visitor_id' => 'vis-123',
            'email' => 'user@example.com',
        ];

        PayloadValidator::validate('contacts/identify', $payload);

        $this->assertTrue(true);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function rejeita_payload_vazio(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Payload vazio');

        PayloadValidator::validate('events', []);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function rejeita_evento_sem_event_id(): void
    {
        $payload = [
            'event_name' => 'product.viewed',
            'visitor_id' => 'vis-123',
            'occurred_at' => now()->toIso8601String(),
        ];

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('event_id');

        PayloadValidator::validate('events', $payload);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function rejeita_evento_sem_event_name(): void
    {
        $payload = [
            'event_id' => 'evt-123',
            'visitor_id' => 'vis-123',
            'occurred_at' => now()->toIso8601String(),
        ];

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('event_name');

        PayloadValidator::validate('events', $payload);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function rejeita_event_name_com_formato_invalido(): void
    {
        $payload = [
            'event_id' => 'evt-123',
            'event_name' => 'invalid-format-without-dots',
            'visitor_id' => 'vis-123',
            'occurred_at' => now()->toIso8601String(),
        ];

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('entidade.acao');

        PayloadValidator::validate('events', $payload);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function rejeita_identify_sem_identificador(): void
    {
        $payload = [
            'visitor_id' => 'vis-123',
        ];

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('external_id ou email');

        PayloadValidator::validate('contacts/identify', $payload);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function rejeita_payload_com_dados_sensíveis(): void
    {
        $payload = [
            'event_id' => 'evt-123',
            'event_name' => 'purchase.completed',
            'visitor_id' => 'vis-123',
            'occurred_at' => now()->toIso8601String(),
            'credit_card' => '1234-5678-9012-3456',
        ];

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('sensível');

        PayloadValidator::validate('events', $payload);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function rejeita_payload_muito_grande(): void
    {
        $payload = [
            'event_id' => 'evt-123',
            'event_name' => 'product.viewed',
            'visitor_id' => 'vis-123',
            'occurred_at' => now()->toIso8601String(),
            'properties' => [
                'large_data' => str_repeat('x', 20_000),
            ],
        ];

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('excede tamanho máximo');

        PayloadValidator::validate('events', $payload);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function retorna_debug_info_sem_dados_sensíveis(): void
    {
        $payload = [
            'event_id' => 'evt-123',
            'event_name' => 'product.viewed',
            'visitor_id' => 'vis-123',
            'properties' => ['foo' => 'bar'],
        ];

        $debug = PayloadValidator::debug($payload);

        $this->assertIsArray($debug);
        $this->assertArrayHasKey('field_count', $debug);
        $this->assertArrayHasKey('size_bytes', $debug);
        $this->assertArrayHasKey('keys', $debug);
        $this->assertEquals(4, $debug['field_count']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function rejeita_endpoint_desconhecido(): void
    {
        $payload = [
            'event_id' => 'evt-123',
        ];

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Endpoint desconhecido');

        PayloadValidator::validate('unknown/endpoint', $payload);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function rejeita_dados_sensíveis_recursivamente(): void
    {
        $payload = [
            'event_id' => 'evt-123',
            'event_name' => 'purchase.completed',
            'visitor_id' => 'vis-123',
            'occurred_at' => now()->toIso8601String(),
            'properties' => [
                'nested' => [
                    'password' => 'secret123',
                ],
            ],
        ];

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('sensível');

        PayloadValidator::validate('events', $payload);
    }
}
