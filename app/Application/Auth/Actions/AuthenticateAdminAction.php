<?php

namespace App\Application\Auth\Actions;

use App\Models\User;
use App\Support\Audit\AuditLogger;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthenticateAdminAction
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function handle(string $email, string $password, bool $remember = false): User
    {
        $user = User::where('email', $email)->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            $this->auditLogger->log('login.failed', description: "Tentativa de login com e-mail: {$email}");

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        if (! $user->is_active) {
            $this->auditLogger->log('login.blocked', $user, description: 'Tentativa de login em conta desativada.');

            throw ValidationException::withMessages([
                'email' => 'Esta conta está desativada.',
            ]);
        }

        Auth::login($user, $remember);

        if (request()->hasSession()) {
            request()->session()->regenerate();
        }

        $user->forceFill(['last_login_at' => now()])->save();

        $this->auditLogger->log('login', $user);

        return $user;
    }
}
