<?php

namespace App\Application\Tenants\Actions;

use App\Models\Tenant;
use App\Support\Audit\AuditLogger;
use Illuminate\Validation\ValidationException;

class DeleteTenantAction
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function handle(Tenant $tenant): void
    {
        if ($tenant->applications()->exists()) {
            throw ValidationException::withMessages([
                'tenant' => 'Não é possível excluir um tenant que possui aplicações vinculadas.',
            ]);
        }

        $this->auditLogger->log('tenant.deleted', $tenant, [
            'name' => $tenant->name,
            'slug' => $tenant->slug,
        ]);

        $tenant->delete();
    }
}
