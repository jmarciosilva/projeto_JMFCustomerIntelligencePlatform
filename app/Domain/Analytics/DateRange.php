<?php

namespace App\Domain\Analytics;

use Illuminate\Support\Carbon;

readonly class DateRange
{
    public function __construct(
        public Carbon $from,
        public Carbon $to,
    ) {}

    public static function today(): self
    {
        return new self(now()->startOfDay(), now()->endOfDay());
    }

    /**
     * Últimos $days dias, incluindo hoje.
     */
    public static function lastDays(int $days): self
    {
        return new self(now()->subDays($days - 1)->startOfDay(), now()->endOfDay());
    }

    /**
     * Lista de datas (uma por dia) cobertas pelo intervalo, do mais antigo ao mais recente.
     *
     * @return list<Carbon>
     */
    public function dates(): array
    {
        $dates = [];
        $cursor = $this->from->clone()->startOfDay();
        $lastDay = $this->to->clone()->startOfDay();

        while ($cursor->lessThanOrEqualTo($lastDay)) {
            $dates[] = $cursor->clone();
            $cursor->addDay();
        }

        return $dates;
    }
}
