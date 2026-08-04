<?php

namespace App\Application\Applications\Actions;

use App\Models\Application;
use App\Models\Tenant;
use App\Support\Audit\AuditLogger;
use App\Support\Slug\UniqueSlugGenerator;

class CreateApplicationAction
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function handle(Tenant $tenant, string $name): Application
    {
        $slug = UniqueSlugGenerator::generate(
            $name,
            fn (string $slug) => Application::where('tenant_id', $tenant->id)->where('slug', $slug)->exists()
        );

        $application = Application::create([
            'tenant_id' => $tenant->id,
            'name' => $name,
            'slug' => $slug,
            'is_active' => true,
        ]);

        $this->auditLogger->log('application.created', $application, [
            'name' => $name,
            'slug' => $slug,
            'tenant_id' => $tenant->id,
        ]);

        return $application;
    }
}
