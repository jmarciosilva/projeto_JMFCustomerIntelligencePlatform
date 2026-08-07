<?php

namespace JmfSystem\CustomerIntelligence\Tests;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use JmfSystem\CustomerIntelligence\Jobs\SendPayloadJob;
use Orchestra\Testbench\TestCase;
use PHPUnit\Framework\Attributes\Test;

class SendPayloadJobTest extends TestCase
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
    public function envia_payload_com_sucesso_quando_resposta_200(): void
    {
        Http::fake([
            'https://ci.example.com/api/v1/events' => Http::response(['id' => '123'], 200),
        ]);

        config([
            'customer-intelligence.base_url' => 'https://ci.example.com/api/v1',
            'customer-intelligence.token' => 'test-token',
        ]);

        $job = new SendPayloadJob('events', [
            'event_id' => 'evt-123',
            'event_name' => 'product.viewed',
            'visitor_id' => 'vis-123',
        ]);

        $job->handle();

        Http::assertSent(function ($request) {
            return $request->url() === 'https://ci.example.com/api/v1/events'
                && $request->hasHeader('Authorization', 'Bearer test-token');
        });
    }

    #[Test]
    public function relanca_excecao_em_erro_5xx_para_retry(): void
    {
        Http::fake([
            'https://ci.example.com/api/v1/events' => Http::response([], 500),
        ]);

        config([
            'customer-intelligence.base_url' => 'https://ci.example.com/api/v1',
            'customer-intelligence.token' => 'test-token',
        ]);

        $job = new SendPayloadJob('events', [
            'event_id' => 'evt-123',
            'event_name' => 'product.viewed',
            'visitor_id' => 'vis-123',
        ]);

        $this->expectException(RequestException::class);

        $job->handle();
    }

    #[Test]
    public function relanca_excecao_em_429_para_retry(): void
    {
        Http::fake([
            'https://ci.example.com/api/v1/events' => Http::response([], 429),
        ]);

        config([
            'customer-intelligence.base_url' => 'https://ci.example.com/api/v1',
            'customer-intelligence.token' => 'test-token',
        ]);

        $job = new SendPayloadJob('events', [
            'event_id' => 'evt-123',
            'event_name' => 'product.viewed',
            'visitor_id' => 'vis-123',
        ]);

        $this->expectException(RequestException::class);

        $job->handle();
    }

    #[Test]
    public function nao_relanca_excecao_em_erro_422_de_validacao(): void
    {
        Http::fake([
            'https://ci.example.com/api/v1/events' => Http::response(
                ['errors' => ['event_name' => 'Invalid format']],
                422
            ),
        ]);

        config([
            'customer-intelligence.base_url' => 'https://ci.example.com/api/v1',
            'customer-intelligence.token' => 'test-token',
        ]);

        $job = new SendPayloadJob('events', [
            'event_id' => 'evt-123',
            'event_name' => 'invalid-format',
            'visitor_id' => 'vis-123',
        ]);

        $job->handle();

        $this->assertTrue(true);
    }

    #[Test]
    public function nao_relanca_excecao_em_erro_401_de_autenticacao(): void
    {
        Http::fake([
            'https://ci.example.com/api/v1/events' => Http::response(['error' => 'Unauthorized'], 401),
        ]);

        config([
            'customer-intelligence.base_url' => 'https://ci.example.com/api/v1',
            'customer-intelligence.token' => 'invalid-token',
        ]);

        $job = new SendPayloadJob('events', [
            'event_id' => 'evt-123',
            'event_name' => 'product.viewed',
            'visitor_id' => 'vis-123',
        ]);

        $job->handle();

        $this->assertTrue(true);
    }

    #[Test]
    public function usa_exponential_backoff_para_retries(): void
    {
        config(['customer-intelligence.backoff' => [5, 30, 120]]);

        $job = new SendPayloadJob('events', [
            'event_id' => 'evt-123',
            'event_name' => 'product.viewed',
        ]);

        $backoff = $job->backoff();

        $this->assertEquals([5, 30, 120], $backoff);
    }

    #[Test]
    public function valida_payload_antes_de_enviar(): void
    {
        config([
            'customer-intelligence.base_url' => 'https://ci.example.com/api/v1',
            'customer-intelligence.token' => 'test-token',
        ]);

        $job = new SendPayloadJob('events', []);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Payload vazio');

        $job->handle();
    }

    #[Test]
    public function resposta_com_sucesso_retorna_sem_excecao(): void
    {
        Http::fake([
            'https://ci.example.com/api/v1/contacts/identify' => Http::response([], 200),
        ]);

        config([
            'customer-intelligence.base_url' => 'https://ci.example.com/api/v1',
            'customer-intelligence.token' => 'test-token',
        ]);

        $job = new SendPayloadJob('contacts/identify', [
            'event_id' => 'evt-123',
            'visitor_id' => 'vis-123',
            'email' => 'user@example.com',
        ]);

        $job->handle();

        $this->assertTrue(true);
    }

    #[Test]
    public function usa_numero_de_tentativas_da_configuracao(): void
    {
        config(['customer-intelligence.tries' => 5]);

        $job = new SendPayloadJob('events', [
            'event_id' => 'evt-123',
        ]);

        $this->assertEquals(5, $job->tries);
    }
}
