<?php

namespace App\Application\Affiliate\Actions;

use App\Models\AffiliateProgram;
use App\Models\Application;
use App\Support\Audit\AuditLogger;
use App\Support\Slug\UniqueSlugGenerator;

class CreateAffiliateProgramAction
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(Application $application, array $data): AffiliateProgram
    {
        $slug = UniqueSlugGenerator::generate(
            $data['name'],
            fn (string $slug) => AffiliateProgram::where('application_id', $application->id)->where('slug', $slug)->exists()
        );

        $program = AffiliateProgram::create([
            'application_id' => $application->id,
            'name' => $data['name'],
            'slug' => $slug,
            'website' => $data['website'] ?? null,
            'description' => $data['description'] ?? null,
            'provider' => $data['provider'] ?? AffiliateProgram::PROVIDER_MANUAL,
            'status' => $data['status'] ?? AffiliateProgram::STATUS_ACTIVE,
        ]);

        $this->auditLogger->log('affiliate_program.created', $program, [
            'name' => $program->name,
            'provider' => $program->provider,
        ]);

        return $program;
    }
}
