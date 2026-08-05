<?php

namespace JmfSystem\CustomerIntelligence\Facades;

use Illuminate\Support\Facades\Facade;
use JmfSystem\CustomerIntelligence\Client;

/**
 * @method static void identify(array $traits = [], array $consents = [])
 * @method static void track(string $eventName, array $properties = [], ?string $subjectType = null, string|int|null $subjectId = null)
 * @method static void conversion(string $eventName, array $properties = [])
 *
 * @see Client
 */
class CustomerIntelligence extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return Client::class;
    }
}
