<?php

namespace App\Application\Users\Actions;

use App\Models\User;
use App\Support\Audit\AuditLogger;

class UpdateProfileAction
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function handle(User $user, string $name, ?string $newPassword = null): User
    {
        $before = $user->only(['name']);

        $user->name = $name;

        if (filled($newPassword)) {
            $user->password = $newPassword;
        }

        $user->save();

        $this->auditLogger->log('profile.updated', $user, [
            'before' => $before,
            'after' => $user->only(['name']),
            'password_changed' => filled($newPassword),
        ]);

        return $user;
    }
}
