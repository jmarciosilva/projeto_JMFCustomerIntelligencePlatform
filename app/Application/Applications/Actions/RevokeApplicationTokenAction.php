<?php

namespace App\Application\Applications\Actions;

use App\Models\Application;
use App\Support\Audit\AuditLogger;

class RevokeApplicationTokenAction
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function handle(Application $application, int $tokenId): void
    {
        $token = $application->tokens()->findOrFail($tokenId);

        $this->auditLogger->log('application_token.revoked', $application, [
            'token_name' => $token->name,
            'token_id' => $token->id,
        ]);

        $token->delete();
    }
}
