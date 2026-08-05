<?php

namespace App\Application\Applications\Actions;

use App\Models\Application;
use App\Support\Audit\AuditLogger;

class UpdateApplicationAction
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function handle(Application $application, string $name, ?string $conversionEventName = null): Application
    {
        $before = $application->only(['name', 'conversion_event_name']);

        $application->name = $name;
        $application->conversion_event_name = $conversionEventName;
        $application->save();

        $this->auditLogger->log('application.updated', $application, [
            'before' => $before,
            'after' => $application->only(['name', 'conversion_event_name']),
        ]);

        return $application;
    }
}
