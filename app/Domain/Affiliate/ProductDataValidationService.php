<?php

namespace App\Domain\Affiliate;

use App\Models\AffiliateProduct;

class ProductDataValidationService
{
    public function __construct(private int $stalenessThresholdDays = 7) {}

    /**
     * Verifica se os dados de um produto estão desatualizados
     */
    public function isDataStale(AffiliateProduct $product): bool
    {
        $threshold = now()->subDays($this->stalenessThresholdDays);

        $isPriceStale = $product->price_checked_at === null
            || $product->price_checked_at->lessThan($threshold);

        $isAvailabilityStale = $product->availability_checked_at === null
            || $product->availability_checked_at->lessThan($threshold);

        return $isPriceStale || $isAvailabilityStale;
    }

    /**
     * Verifica se os dados de um produto estão atualizados
     */
    public function isDataFresh(AffiliateProduct $product): bool
    {
        return ! $this->isDataStale($product);
    }

    /**
     * Retorna o número de dias desde a última verificação de preço
     */
    public function daysSincePriceCheck(AffiliateProduct $product): ?int
    {
        if ($product->price_checked_at === null) {
            return null;
        }

        return (int) $product->price_checked_at->diffInDays(now());
    }

    /**
     * Retorna o número de dias desde a última verificação de disponibilidade
     */
    public function daysSinceAvailabilityCheck(AffiliateProduct $product): ?int
    {
        if ($product->availability_checked_at === null) {
            return null;
        }

        return (int) $product->availability_checked_at->diffInDays(now());
    }
}
