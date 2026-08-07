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
        Log::fake();

        $payload = ['event_id' => 'evt-123', 'visitor_id' => 'vis-123'];
        $logger = new PayloadLogger('events', $payload);

        $logger->success(200, 0.05);

        Log::assertLogged('info', function ($message, $context) {
            return $context['status'] === 200
                && $context['event_id'] === 'evt-123'
                && isset($context['trace_id'])
                && isset($context['duration_ms']);
        });
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function loga_erro_retentável(): void
    {
        Log::fake();

        $payload = ['event_id' => 'evt-123'];
        $logger = new PayloadLogger('events', $payload);

        $logger->retryable(500, 1, 3, 'Server error');

        Log::assertLogged('warning', function ($message, $context) {
            return $context['status'] === 500
                && $context['attempt'] === 1
                && $context['max_attempts'] === 3
                && isset($context['trace_id']);
        });
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function loga_erro_permanente(): void
    {
        Log::fake();

        $payload = ['event_id' => 'evt-123'];
        $logger = new PayloadLogger('contacts/identify', $payload);

        $logger->permanent(422, 'Validation failed');

        Log::assertLogged('warning', function ($message, $context) {
            return $context['status'] === 422
                && str_contains($context['reason'], 'Validation')
                && isset($context['trace_id']);
        });
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function loga_falha_final(): void
    {
        Log::fake();

        $payload = ['event_id' => 'evt-123'];
        $logger = new PayloadLogger('events', $payload);
        $exception = new \Exception('Test error');

        $logger->failed(3, $exception);

        Log::assertLogged('error', function ($message, $context) {
            return $context['attempts'] === 3
                && $context['exception_class'] === 'Exception'
                && $context['exception_message'] === 'Test error'
                && isset($context['trace_id']);
        });
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function loga_erro_validacao(): void
    {
        Log::fake();

        $payload = ['event_id' => 'evt-123'];
        $logger = new PayloadLogger('events', $payload);

        $logger->validationError('Invalid event name format');

        Log::assertLogged('error', function ($message, $context) {
            return str_contains($context['reason'], 'Invalid')
                && isset($context['payload_debug'])
                && isset($context['trace_id']);
        });
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function loga_erro_rede(): void
    {
        Log::fake();

        $payload = ['event_id' => 'evt-123'];
        $logger = new PayloadLogger('events', $payload);

        $logger->networkError('Connection timeout', 1);

        Log::assertLogged('warning', function ($message, $context) {
            return str_contains($context['reason'], 'timeout')
                && $context['attempt'] === 1
                && isset($context['trace_id'])
                && isset($context['next_retry_in_seconds']);
        });
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function loga_erro_configuracao(): void
    {
        Log::fake();

        $payload = ['event_id' => 'evt-123'];
        $logger = new PayloadLogger('events', $payload);

        $logger->configError('Missing JMF_CI_BASE_URL');

        Log::assertLogged('error', function ($message, $context) {
            return str_contains($context['reason'], 'Missing')
                && isset($context['trace_id']);
        });
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function inclui_informacoes_corretas_no_contexto(): void
    {
        Log::fake();

        $payload = [
            'event_id' => 'evt-456',
            'visitor_id' => 'vis-789',
        ];
        $logger = new PayloadLogger('events', $payload);

        $logger->success(200, 0.1);

        Log::assertLogged('info', function ($message, $context) {
            return $context['endpoint'] === 'events'
                && $context['event_id'] === 'evt-456'
                && $context['visitor_id'] === 'vis-789'
                && isset($context['timestamp'])
                && isset($context['sdk_version']);
        });
    }
}
