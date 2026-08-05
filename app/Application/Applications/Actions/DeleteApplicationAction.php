<?php

namespace App\Application\Applications\Actions;

use App\Models\Application;
use App\Support\Audit\AuditLogger;
use Illuminate\Validation\ValidationException;

class DeleteApplicationAction
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function handle(Application $application): void
    {
        if ($application->events()->exists()) {
            throw ValidationException::withMessages([
                'application' => 'Não é possível excluir uma aplicação que possui eventos vinculados.',
            ]);
        }

        if ($application->visitors()->exists()) {
            throw ValidationException::withMessages([
                'application' => 'Não é possível excluir uma aplicação que possui visitantes vinculados.',
            ]);
        }

        $this->auditLogger->log('application.deleted', $application, [
            'name' => $application->name,
            'slug' => $application->slug,
        ]);

        $application->tokens()->delete();
        $application->delete();
    }
}
