<?php

namespace App\Application\Applications\Actions;

use App\Models\Application;
use App\Support\Audit\AuditLogger;

class ToggleApplicationActiveAction
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function handle(Application $application): Application
    {
        $application->is_active = ! $application->is_active;
        $application->save();

        $this->auditLogger->log($application->is_active ? 'application.activated' : 'application.deactivated', $application);

        return $application;
    }
}
