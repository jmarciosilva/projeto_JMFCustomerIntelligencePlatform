<?php

namespace App\Domain\Shared\Enums;

enum ConsentPurpose: string
{
    case Marketing = 'marketing';
    case Analytics = 'analytics';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(fn (self $purpose) => $purpose->value, self::cases());
    }
}
