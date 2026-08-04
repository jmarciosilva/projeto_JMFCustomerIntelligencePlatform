<?php

namespace App\Application\Users\Actions;

use App\Models\User;
use App\Support\Audit\AuditLogger;

class CreateUserAction
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    /**
     * @param  list<string>  $roles
     */
    public function handle(string $name, string $email, string $password, array $roles = []): User
    {
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'is_active' => true,
        ]);

        if ($roles !== []) {
            $user->syncRoles($roles);
        }

        $this->auditLogger->log('user.created', $user, [
            'name' => $name,
            'email' => $email,
            'roles' => $roles,
        ]);

        return $user;
    }
}
