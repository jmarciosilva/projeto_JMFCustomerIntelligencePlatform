<?php

namespace App\Domain\Affiliate;

use App\Domain\Affiliate\Contracts\AffiliateProviderInterface;
use App\Models\AffiliateProgram;

/**
 * Provider padrão quando o programa de afiliados não expõe API oficial:
 * produtos são cadastrados manualmente ou importados via CSV, nunca
 * buscados automaticamente.
 */
class ManualAffiliateProvider implements AffiliateProviderInterface
{
    public function key(): string
    {
        return AffiliateProgram::PROVIDER_MANUAL;
    }

    public function isConfigured(): bool
    {
        return true;
    }

    public function fetchProducts(AffiliateProgram $program): array
    {
        return [];
    }
}
