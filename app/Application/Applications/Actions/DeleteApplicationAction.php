<?php

namespace App\Application\Applications\Actions;

use App\Models\Application;
use App\Support\Audit\AuditLogger;

class DeleteApplicationAction
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function handle(Application $application): void
    {
        $this->auditLogger->log('application.deleted', $application, [
            'name' => $application->name,
            'slug' => $application->slug,
        ]);

        $application->tokens()->delete();
        $application->delete();
    }
}
