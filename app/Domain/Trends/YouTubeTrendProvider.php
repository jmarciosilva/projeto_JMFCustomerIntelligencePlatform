<?php

namespace App\Domain\Trends;

use App\Domain\Trends\Contracts\TrendProviderInterface;
use App\Domain\Trends\Exceptions\ProviderNotConfiguredException;
use App\Models\Trend;
use App\Models\TrendSnapshot;

/**
 * Stub documentado. A YouTube Data API v3 tem endpoint oficial de busca
 * (`search.list`), mas exige projeto no Google Cloud Console com API key
 * própria e está sujeita a cota diária de uso — nenhuma configuração foi
 * feita ainda neste projeto. Ponto de extensão pronto para quando a chave
 * de API for provisionada.
 */
class YouTubeTrendProvider implements TrendProviderInterface
{
    public function key(): string
    {
        return TrendSnapshot::SOURCE_YOUTUBE;
    }

    public function isConfigured(): bool
    {
        return false;
    }

    public function collect(Trend $trend): ?array
    {
        throw new ProviderNotConfiguredException(
            'Integração com YouTube Data API v3 não configurada — defina uma API key do Google Cloud Console. '.
            'Registre observações manualmente enquanto isso não estiver disponível.'
        );
    }
}
