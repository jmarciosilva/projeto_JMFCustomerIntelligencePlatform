<?php

namespace App\Domain\Affiliate;

use App\Domain\Affiliate\Contracts\AffiliateProviderInterface;
use App\Domain\Affiliate\Exceptions\ProviderNotConfiguredException;
use App\Models\AffiliateProgram;

/**
 * Stub documentado para o programa "Influenciador Magalu / Magazine Você".
 *
 * Até a data desta implementação não há API pública oficial de catálogo/
 * comissão documentada para o programa (ver ROADMAP.md, Fase 22). Produtos
 * do Magalu devem ser cadastrados manualmente ou importados via CSV
 * (`ManualAffiliateProvider`) enquanto isso não mudar. Esta classe existe
 * apenas para já ter o ponto de extensão pronto: quando a Magazine Luiza
 * documentar oficialmente uma API de afiliados, a implementação real entra
 * aqui, sem qualquer mudança no restante do módulo (Product Matcher,
 * Opportunity Engine, etc. dependem apenas de `AffiliateProviderInterface`).
 */
class MagaluAffiliateProvider implements AffiliateProviderInterface
{
    public function key(): string
    {
        return AffiliateProgram::PROVIDER_MAGALU;
    }

    public function isConfigured(): bool
    {
        return false;
    }

    public function fetchProducts(AffiliateProgram $program): array
    {
        throw new ProviderNotConfiguredException(
            'Não há API pública oficial de afiliados do Magalu/Magazine Você documentada. '.
            'Cadastre os produtos manualmente ou importe via CSV.'
        );
    }
}
