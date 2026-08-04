<?php

namespace App\Application\Tenants\Actions;

use App\Models\Tenant;
use App\Support\Audit\AuditLogger;

class ToggleTenantActiveAction
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function handle(Tenant $tenant): Tenant
    {
        $tenant->is_active = ! $tenant->is_active;
        $tenant->save();

        $this->auditLogger->log($tenant->is_active ? 'tenant.activated' : 'tenant.deactivated', $tenant);

        return $tenant;
    }
}
