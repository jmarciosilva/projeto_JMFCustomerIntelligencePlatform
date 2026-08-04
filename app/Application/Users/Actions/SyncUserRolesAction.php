<?php

namespace App\Application\Users\Actions;

use App\Models\User;
use App\Support\Audit\AuditLogger;

class SyncUserRolesAction
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    /**
     * @param  list<string>  $roles
     */
    public function handle(User $user, array $roles): User
    {
        $before = $user->getRoleNames()->all();

        $user->syncRoles($roles);

        $this->auditLogger->log('user.roles_synced', $user, [
            'before' => $before,
            'after' => $roles,
        ]);

        return $user;
    }
}
