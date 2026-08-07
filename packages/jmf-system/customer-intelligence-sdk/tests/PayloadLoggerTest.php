<?php

namespace JmfSystem\CustomerIntelligence\Tests;

use Illuminate\Support\Facades\Log;
use JmfSystem\CustomerIntelligence\PayloadLogger;
use Orchestra\Testbench\TestCase;

class PayloadLoggerTest extends TestCase
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
    public function gera_trace_id_unico(): void
    {
        $logger1 = new PayloadLogger('events', ['event_id' => '1']);
        $logger2 = new PayloadLogger('events', ['event_id' => '2']);

        $this->assertNotEquals($logger1->getTraceId(), $logger2->getTraceId());
        $this->assertNotEmpty($logger1->getTraceId());
        $this->assertNotEmpty($logger2->getTraceId());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function loga_sucesso_com_contexto(): void
    {
        Log::spy();

        $payload = ['event_id' => 'evt-123', 'visitor_id' => 'vis-123'];
        $logger = new PayloadLogger('events', $payload);

        $logger->success(200, 0.05);

        Log::shouldHaveReceived('info')->once();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function loga_erro_retentável(): void
    {
        Log::spy();

        $payload = ['event_id' => 'evt-123'];
        $logger = new PayloadLogger('events', $payload);

        $logger->retryable(500, 1, 3, 'Server error');

        Log::shouldHaveReceived('warning')->once();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function loga_erro_permanente(): void
    {
        Log::spy();

        $payload = ['event_id' => 'evt-123'];
        $logger = new PayloadLogger('contacts/identify', $payload);

        $logger->permanent(422, 'Validation failed');

        Log::shouldHaveReceived('warning')->once();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function loga_falha_final(): void
    {
        Log::spy();

        $payload = ['event_id' => 'evt-123'];
        $logger = new PayloadLogger('events', $payload);
        $exception = new \Exception('Test error');

        $logger->failed(3, $exception);

        Log::shouldHaveReceived('error')->once();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function loga_erro_validacao(): void
    {
        Log::spy();

        $payload = ['event_id' => 'evt-123'];
        $logger = new PayloadLogger('events', $payload);

        $logger->validationError('Invalid event name format');

        Log::shouldHaveReceived('error')->once();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function loga_erro_rede(): void
    {
        Log::spy();

        $payload = ['event_id' => 'evt-123'];
        $logger = new PayloadLogger('events', $payload);

        $logger->networkError('Connection timeout', 1);

        Log::shouldHaveReceived('warning')->once();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function loga_erro_configuracao(): void
    {
        Log::spy();

        $payload = ['event_id' => 'evt-123'];
        $logger = new PayloadLogger('events', $payload);

        $logger->configError('Missing JMF_CI_BASE_URL');

        Log::shouldHaveReceived('error')->once();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function inclui_informacoes_corretas_no_contexto(): void
    {
        Log::spy();

        $payload = [
            'event_id' => 'evt-456',
            'visitor_id' => 'vis-789',
        ];
        $logger = new PayloadLogger('events', $payload);

        $logger->success(200, 0.1);

        Log::shouldHaveReceived('info')->once();
    }
}
