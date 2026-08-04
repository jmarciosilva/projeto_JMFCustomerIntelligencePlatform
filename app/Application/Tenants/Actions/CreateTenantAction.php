<?php

namespace App\Application\Tenants\Actions;

use App\Models\Tenant;
use App\Support\Audit\AuditLogger;
use App\Support\Slug\UniqueSlugGenerator;

class CreateTenantAction
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function handle(string $name): Tenant
    {
        $slug = UniqueSlugGenerator::generate(
            $name,
            fn (string $slug) => Tenant::where('slug', $slug)->exists()
        );

        $tenant = Tenant::create([
            'name' => $name,
            'slug' => $slug,
            'is_active' => true,
        ]);

        $this->auditLogger->log('tenant.created', $tenant, [
            'name' => $name,
            'slug' => $slug,
        ]);

        return $tenant;
    }
}
