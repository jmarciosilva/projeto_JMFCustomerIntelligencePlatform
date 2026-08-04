<?php

namespace App\Application\Applications\Actions;

use App\Models\Application;
use App\Support\Audit\AuditLogger;
use Laravel\Sanctum\NewAccessToken;

class RotateApplicationTokenAction
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function handle(Application $application, int $tokenId): NewAccessToken
    {
        $oldToken = $application->tokens()->findOrFail($tokenId);
        $name = $oldToken->name;

        $oldToken->delete();

        $newToken = $application->createToken($name);

        $this->auditLogger->log('application_token.rotated', $application, [
            'token_name' => $name,
            'old_token_id' => $tokenId,
            'new_token_id' => $newToken->accessToken->id,
        ]);

        return $newToken;
    }
}
