<?php

namespace App\Domain\Trends;

use App\Domain\Trends\Contracts\TrendProviderInterface;
use App\Domain\Trends\Exceptions\ProviderNotConfiguredException;
use App\Models\Trend;
use App\Models\TrendSnapshot;

/**
 * Stub documentado. O Google Trends não possui API pública oficial — apenas
 * a interface web em trends.google.com. Bibliotecas não-oficiais (ex.:
 * pytrends) fazem scraping da interface web, o que viola os Termos de Uso
 * do Google, e por isso não são usadas neste projeto (ver README.md/
 * ROADMAP.md, Fase 23). Esta classe existe apenas como ponto de extensão
 * para o dia em que o Google disponibilizar uma API oficial.
 */
class GoogleTrendsProvider implements TrendProviderInterface
{
    public function key(): string
    {
        return TrendSnapshot::SOURCE_GOOGLE_TRENDS;
    }

    public function isConfigured(): bool
    {
        return false;
    }

    public function collect(Trend $trend): ?array
    {
        throw new ProviderNotConfiguredException(
            'Não há API pública oficial do Google Trends. Registre observações manualmente enquanto isso não mudar.'
        );
    }
}
