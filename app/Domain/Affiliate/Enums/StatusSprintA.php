<?php

namespace App\Domain\Affiliate\Enums;

enum StatusSprintA: string
{
    case DISCOVERED = 'DISCOVERED';
    case ANALYZING = 'ANALYZING';
    case APPROVED = 'APPROVED';
    case REJECTED = 'REJECTED';
    case PUBLISHED = 'PUBLISHED';
    case EXPIRED = 'EXPIRED';

    public function label(): string
    {
        return match ($this) {
            self::DISCOVERED => 'Descoberta',
            self::ANALYZING => 'Analisando',
            self::APPROVED => 'Aprovada',
            self::REJECTED => 'Rejeitada',
            self::PUBLISHED => 'Publicada',
            self::EXPIRED => 'Expirada',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::DISCOVERED => 'blue',
            self::ANALYZING => 'yellow',
            self::APPROVED => 'green',
            self::REJECTED => 'red',
            self::PUBLISHED => 'green',
            self::EXPIRED => 'gray',
        };
    }
}
