<?php

namespace App\Listeners;

use App\Domain\Marketplace\ProcessMarketplaceEventAction;
use App\Events\EventWasIngested;

class ProcessMarketplaceEventListener
{
    public function __construct(private ProcessMarketplaceEventAction $action) {}

    public function handle(EventWasIngested $event): void
    {
        $this->action->handle($event->event);
    }
}
