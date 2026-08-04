<?php

namespace App\Application\Users\Actions;

use App\Models\User;
use App\Support\Audit\AuditLogger;

class ToggleUserActiveAction
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function handle(User $user): User
    {
        $user->is_active = ! $user->is_active;
        $user->save();

        $this->auditLogger->log($user->is_active ? 'user.activated' : 'user.deactivated', $user);

        return $user;
    }
}
