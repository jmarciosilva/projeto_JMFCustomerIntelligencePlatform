<?php

namespace App\Enums;

enum MatchStatus: string
{
    case MATCHED = 'matched';
    case NO_MATCH_IN_CATALOG = 'no_match_in_catalog';
    case NO_TREND_DATA = 'no_trend_data';

    public function label(): string
    {
        return match ($this) {
            self::MATCHED => 'Produto encontrado no catálogo',
            self::NO_MATCH_IN_CATALOG => 'Nenhum produto correspondente no catálogo',
            self::NO_TREND_DATA => 'Sem dados de tendência',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::MATCHED => 'green',
            self::NO_MATCH_IN_CATALOG => 'amber',
            self::NO_TREND_DATA => 'gray',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::MATCHED => '✓',
            self::NO_MATCH_IN_CATALOG => '⚠',
            self::NO_TREND_DATA => '○',
        };
    }
}
