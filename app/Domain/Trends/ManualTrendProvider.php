<?php

namespace App\Domain\Trends;

use App\Domain\Trends\Contracts\TrendProviderInterface;
use App\Models\Trend;
use App\Models\TrendSnapshot;

/**
 * Provider "sem coleta automática": o operador registra observações
 * manualmente (contagem de posts vista no Instagram, engajamento percebido,
 * etc.) diretamente na tela de detalhe da tendência — ver
 * `App\Application\Trends\Actions\RegisterManualTrendSnapshotAction`.
 * `collect()` nunca retorna dado novo; existe apenas para completar o
 * contrato e documentar a fonte `TrendSnapshot::SOURCE_MANUAL`.
 */
class ManualTrendProvider implements TrendProviderInterface
{
    public function key(): string
    {
        return TrendSnapshot::SOURCE_MANUAL;
    }

    public function isConfigured(): bool
    {
        return true;
    }

    public function collect(Trend $trend): ?array
    {
        return null;
    }
}
