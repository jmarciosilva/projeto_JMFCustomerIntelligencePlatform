<?php

namespace JmfSystem\CustomerIntelligence\Tests;

use JmfSystem\CustomerIntelligence\CustomerIntelligenceServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    /**
     * @return list<class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [CustomerIntelligenceServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
        $app['config']->set('customer-intelligence.base_url', 'https://ci.test/api/v1');
        $app['config']->set('customer-intelligence.token', 'test-token');
        $app['config']->set('cache.default', 'array');
        $app['config']->set('session.driver', 'array');
        $app['config']->set('queue.default', 'sync');
    }
}
