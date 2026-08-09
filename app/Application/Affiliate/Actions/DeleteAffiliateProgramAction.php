<?php

namespace App\Application\Affiliate\Actions;

use App\Models\AffiliateProgram;
use App\Support\Audit\AuditLogger;
use Illuminate\Validation\ValidationException;

class DeleteAffiliateProgramAction
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function handle(AffiliateProgram $program): void
    {
        if ($program->products()->exists()) {
            throw ValidationException::withMessages([
                'affiliate_program' => 'Não é possível excluir um programa de afiliados que possui produtos vinculados.',
            ]);
        }

        $this->auditLogger->log('affiliate_program.deleted', $program, [
            'name' => $program->name,
            'slug' => $program->slug,
        ]);

        $program->delete();
    }
}
