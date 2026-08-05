<?php

namespace App\Events;

use App\Models\Event;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EventWasIngested
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly Event $event) {}
}
