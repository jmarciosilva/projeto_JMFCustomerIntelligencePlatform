<?php

namespace App\Application\Auth\Actions;

use App\Models\User;
use App\Support\Audit\AuditLogger;
use Illuminate\Support\Facades\Auth;

class LogoutAdminAction
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function handle(): void
    {
        $user = Auth::user();

        if ($user instanceof User) {
            $this->auditLogger->log('logout', $user);
        }

        Auth::guard('web')->logout();

        $session = request()->session();
        $session->invalidate();
        $session->regenerateToken();
    }
}
