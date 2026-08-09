<?php

namespace App\Domain\Affiliate\Contracts;

use App\Domain\Affiliate\Exceptions\ProviderNotConfiguredException;
use App\Models\AffiliateProgram;

interface AffiliateProviderInterface
{
    /**
     * Identificador único do provider (ex.: "manual", "magalu").
     */
    public function key(): string;

    /**
     * Indica se o provider está pronto para buscar produtos automaticamente
     * (ex.: credenciais de API configuradas). Providers sem API oficial
     * disponível devem retornar false — o cadastro continua possível via
     * import manual/CSV.
     */
    public function isConfigured(): bool;

    /**
     * Busca produtos diretamente na API oficial do programa de afiliados.
     *
     * @return list<array<string, mixed>>
     *
     * @throws ProviderNotConfiguredException quando o provider não tem integração oficial disponível.
     */
    public function fetchProducts(AffiliateProgram $program): array;
}
