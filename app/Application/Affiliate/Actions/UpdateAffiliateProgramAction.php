<?php

namespace App\Application\Affiliate\Actions;

use App\Models\AffiliateProgram;
use App\Support\Audit\AuditLogger;

class UpdateAffiliateProgramAction
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(AffiliateProgram $program, array $data): AffiliateProgram
    {
        $before = $program->only(['name', 'website', 'description', 'status']);

        $program->fill([
            'name' => $data['name'],
            'website' => $data['website'] ?? null,
            'description' => $data['description'] ?? null,
            'status' => $data['status'] ?? $program->status,
        ]);
        $program->save();

        $this->auditLogger->log('affiliate_program.updated', $program, [
            'before' => $before,
            'after' => $program->only(['name', 'website', 'description', 'status']),
        ]);

        return $program;
    }
}
