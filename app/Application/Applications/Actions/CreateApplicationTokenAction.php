<?php

namespace App\Application\Applications\Actions;

use App\Models\Application;
use App\Support\Audit\AuditLogger;
use Laravel\Sanctum\NewAccessToken;

class CreateApplicationTokenAction
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function handle(Application $application, string $name): NewAccessToken
    {
        $token = $application->createToken($name);

        $this->auditLogger->log('application_token.created', $application, [
            'token_name' => $name,
            'token_id' => $token->accessToken->id,
        ]);

        return $token;
    }
}
