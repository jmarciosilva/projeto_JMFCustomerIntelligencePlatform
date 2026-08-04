<?php

namespace App\Application\Applications\Actions;

use App\Models\Application;
use App\Support\Audit\AuditLogger;

class UpdateApplicationAction
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function handle(Application $application, string $name): Application
    {
        $before = $application->only(['name']);

        $application->name = $name;
        $application->save();

        $this->auditLogger->log('application.updated', $application, [
            'before' => $before,
            'after' => $application->only(['name']),
        ]);

        return $application;
    }
}
