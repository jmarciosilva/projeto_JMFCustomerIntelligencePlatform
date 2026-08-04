<?php

namespace App\Application\Users\Actions;

use App\Models\User;
use App\Support\Audit\AuditLogger;

class DeleteUserAction
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function handle(User $user): void
    {
        $this->auditLogger->log('user.deleted', $user, [
            'name' => $user->name,
            'email' => $user->email,
        ]);

        $user->delete();
    }
}
