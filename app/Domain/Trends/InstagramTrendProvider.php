<?php

namespace App\Domain\Trends;

use App\Domain\Trends\Contracts\TrendProviderInterface;
use App\Domain\Trends\Exceptions\ProviderNotConfiguredException;
use App\Models\Trend;
use App\Models\TrendSnapshot;

/**
 * Stub documentado. A Instagram Graph API só permite busca de hashtag a
 * partir de uma conta Business/Creator conectada a um Facebook App revisado
 * pela Meta (App Review), com limite de 30 hashtags únicas/semana e retorno
 * apenas de mídia recente — sem métricas históricas de tendência prontas.
 * Até esse processo ser concluído, monitorar hashtags do Instagram é feito
 * via `ManualTrendProvider` (observação manual). Esta classe existe apenas
 * como ponto de extensão: quando o App Review estiver aprovado, a
 * implementação real entra aqui, sem alterar o restante do módulo.
 */
class InstagramTrendProvider implements TrendProviderInterface
{
    public function key(): string
    {
        return TrendSnapshot::SOURCE_INSTAGRAM;
    }

    public function isConfigured(): bool
    {
        return false;
    }

    public function collect(Trend $trend): ?array
    {
        throw new ProviderNotConfiguredException(
            'Integração com Instagram Graph API não configurada — exige App Review da Meta e conta Business/Creator conectada. '.
            'Registre observações manualmente enquanto isso não estiver disponível.'
        );
    }
}
