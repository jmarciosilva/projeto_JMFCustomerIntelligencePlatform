<?php

namespace App\Providers;

use App\Events\EventWasIngested;
use App\Listeners\ProcessMarketplaceEventListener;
use App\Listeners\ResolveVisitorAndSessionListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        EventWasIngested::class => [
            ResolveVisitorAndSessionListener::class,
            ProcessMarketplaceEventListener::class,
        ],
    ];

    public function boot(): void {}
}
