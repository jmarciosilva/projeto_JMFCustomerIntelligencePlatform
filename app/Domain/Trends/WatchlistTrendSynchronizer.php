<?php

namespace App\Domain\Trends;

use App\Models\Trend;
use App\Models\Watchlist;

/**
 * Expande as palavras-chave/hashtags de uma Watchlist em registros `Trend`
 * individuais (uma série histórica por termo). Nunca apaga um `Trend`
 * existente — termos removidos da Watchlist ficam `status=inactive`,
 * preservando o histórico de `TrendSnapshot` já coletado.
 */
class WatchlistTrendSynchronizer
{
    public function sync(Watchlist $watchlist): void
    {
        $desired = $this->desiredTerms($watchlist);

        foreach ($desired as $term => $type) {
            Trend::updateOrCreate(
                ['watchlist_id' => $watchlist->id, 'term' => $term],
                ['application_id' => $watchlist->application_id, 'type' => $type, 'status' => Trend::STATUS_ACTIVE]
            );
        }

        $terms = array_keys($desired);

        $query = Trend::where('watchlist_id', $watchlist->id);

        if ($terms !== []) {
            $query->whereNotIn('term', $terms);
        }

        $query->update(['status' => Trend::STATUS_INACTIVE]);
    }

    /**
     * @return array<string, string> termo => tipo
     */
    private function desiredTerms(Watchlist $watchlist): array
    {
        $desired = [];

        foreach ($watchlist->keywords ?? [] as $keyword) {
            $keyword = trim((string) $keyword);

            if ($keyword !== '') {
                $desired[$keyword] = Trend::TYPE_KEYWORD;
            }
        }

        foreach ($watchlist->hashtags ?? [] as $hashtag) {
            $hashtag = trim((string) $hashtag);

            if ($hashtag !== '') {
                $desired[$hashtag] = Trend::TYPE_HASHTAG;
            }
        }

        return $desired;
    }
}
