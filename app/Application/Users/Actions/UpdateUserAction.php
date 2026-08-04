<?php

namespace App\Application\Users\Actions;

use App\Models\User;
use App\Support\Audit\AuditLogger;

class UpdateUserAction
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function handle(User $user, string $name, string $email, ?string $password = null): User
    {
        $before = $user->only(['name', 'email']);

        $user->name = $name;
        $user->email = $email;

        if (filled($password)) {
            $user->password = $password;
        }

        $user->save();

        $this->auditLogger->log('user.updated', $user, [
            'before' => $before,
            'after' => $user->only(['name', 'email']),
        ]);

        return $user;
    }
}
