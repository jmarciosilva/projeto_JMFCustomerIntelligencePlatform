<?php

namespace App\Support\Audit;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLogger
{
    /**
     * @param  array<string, mixed>  $changes
     */
    public function log(string $action, ?Model $subject = null, array $changes = [], ?string $description = null, ?Authenticatable $causer = null): AuditLog
    {
        $causer ??= Auth::user();

        return AuditLog::create([
            'user_id' => $causer instanceof User ? $causer->id : null,
            'action' => $action,
            'auditable_type' => $subject?->getMorphClass(),
            'auditable_id' => $subject?->getKey(),
            'description' => $description,
            'changes' => $changes === [] ? null : $changes,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }
}
