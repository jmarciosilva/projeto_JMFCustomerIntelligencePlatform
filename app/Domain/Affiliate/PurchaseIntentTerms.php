<?php

namespace App\Domain\Affiliate;

/**
 * Vocabulário centralizado para classificação de intenção de compra.
 * Sprint A MVP: Classificação determinística baseada em keywords.
 */
class PurchaseIntentTerms
{
    // Termos informativos: reduzem intenção de compra
    const INFORMATIONAL = [
        'como', 'how', 'funciona', 'works', 'tutorial', 'guia', 'guide',
        'manual', 'o que é', 'what is', 'explicação', 'explanation',
        'por que', 'why', 'porquê', 'análise', 'analysis',
    ];

    // Termos de investigação comercial: sugerem avaliação
    const INVESTIGATION = [
        'melhor', 'best', 'vs', 'versus', 'comparação', 'comparison',
        'review', 'resenha', 'custo benefício', 'cost benefit', 'worth',
        'vale a pena', 'recomendação', 'recommendation',
    ];

    // Termos transacionais: indicam intenção de compra
    const TRANSACTIONAL = [
        'comprar', 'buy', 'purchase', 'preço', 'price', 'caro', 'expensive',
        'promoção', 'promotion', 'oferta', 'offer', 'desconto', 'discount',
        'onde comprar', 'where to buy', 'cupom', 'coupon', 'combo',
    ];

    // Modificadores de intensidade: indicam urgência/ênfase
    const INTENSITY_MODIFIERS = [
        'muito', 'so', 'tudo sobre', 'everything about', 'melhores', 'best ones',
        'agora', 'now', 'hoje', 'today', 'urgente', 'urgent',
        'imperdível', 'must have', 'ótimo', 'great',
    ];

    // Ajustes de score
    const INFORMATIONAL_PENALTY = -10;      // Reduz score
    const INVESTIGATION_BASE = 45;          // Score base para investigação
    const TRANSACTIONAL_BASE = 75;          // Score base para transação
    const MULTIPLE_SIGNALS_BONUS = 15;      // Bônus por múltiplos sinais
    const INTENSITY_BONUS = 5;              // Bônus por intensidade (quando há sinal)
}
