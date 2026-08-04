<?php

namespace App\Application\Tenants\Actions;

use App\Models\Tenant;
use App\Support\Audit\AuditLogger;

class UpdateTenantAction
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function handle(Tenant $tenant, string $name): Tenant
    {
        $before = $tenant->only(['name']);

        $tenant->name = $name;
        $tenant->save();

        $this->auditLogger->log('tenant.updated', $tenant, [
            'before' => $before,
            'after' => $tenant->only(['name']),
        ]);

        return $tenant;
    }
}
