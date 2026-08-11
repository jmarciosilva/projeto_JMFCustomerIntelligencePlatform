<?php

namespace App\Domain\Affiliate\Enums;

enum PurchaseIntentLabel: string
{
    case LOW = 'LOW';
    case MEDIUM = 'MEDIUM';
    case HIGH = 'HIGH';

    public function label(): string
    {
        return match ($this) {
            self::LOW => 'Baixa',
            self::MEDIUM => 'Média',
            self::HIGH => 'Alta',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::LOW => 'red',
            self::MEDIUM => 'yellow',
            self::HIGH => 'green',
        };
    }

    public function scoreRange(): array
    {
        return match ($this) {
            self::LOW => [0, 39],
            self::MEDIUM => [40, 69],
            self::HIGH => [70, 100],
        };
    }
}
