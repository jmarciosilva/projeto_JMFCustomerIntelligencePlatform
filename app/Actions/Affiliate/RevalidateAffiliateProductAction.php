<?php

namespace App\Actions\Affiliate;

use App\Models\AffiliateProduct;

class RevalidateAffiliateProductAction
{
    /**
     * Revalida os dados de um produto de afiliado
     *
     * Para ManualAffiliateProvider (padrão), isso é uma operação simbólica (noop)
     * que apenas atualiza os timestamps para indicar que o produto foi verificado.
     * Futuros providers com API real podem implementar chamadas reais de revalidação.
     */
    public function execute(AffiliateProduct $product): AffiliateProduct
    {
        $product->update([
            'price_checked_at' => now(),
            'availability_checked_at' => now(),
        ]);

        return $product;
    }
}
