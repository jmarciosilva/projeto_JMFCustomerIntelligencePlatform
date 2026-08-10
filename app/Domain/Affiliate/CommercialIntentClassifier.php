<?php

namespace App\Domain\Affiliate;

use App\Models\ProductOpportunity;

class CommercialIntentClassifier
{
    /**
     * Palavras-chave que indicam alta intenção de compra
     */
    private array $highIntentKeywords = [
        'comprar', 'buy', 'purchase', 'price', 'preço', 'quanto custa',
        'valor', 'melhor', 'mais barato', 'desconto', 'promoção', 'oferta',
        'deal', 'sale', 'cheap', 'affordable', 'where to buy', 'onde comprar',
        'product review', 'análise de produto', 'review de', 'resenha',
        'best price', 'how much', 'quanto', 'custo',
    ];

    /**
     * Palavras-chave que indicam média intenção de compra
     */
    private array $mediumIntentKeywords = [
        'guia', 'guide', 'tutorial', 'how to', 'como', 'o que', 'what is',
        'top 10', 'best of', 'ranking', 'categoria', 'category', 'tipos de',
        'tipos', 'tipos de', 'alternativas', 'alternatives', 'comparar',
        'compare', 'versus', 'vs', 'análise', 'analysis', 'overview',
    ];

    /**
     * Palavras-chave que indicam baixa intenção de compra
     */
    private array $lowIntentKeywords = [
        'o que é', 'what is', 'definição', 'definition', 'significado',
        'meaning', 'história', 'history', 'origem', 'como funciona',
        'how does it work', 'explicação', 'explanation', 'curiosidades',
        'facts', 'interesting', 'notícia', 'news', 'artigo', 'article',
    ];

    /**
     * Classifica intenção comercial de um termo
     *
     * Ordem de prioridade: LOW → MEDIUM → HIGH
     * (mais específico primeiro para evitar ambiguidade)
     *
     * @return ProductOpportunity::INTENT_HIGH|ProductOpportunity::INTENT_MEDIUM|ProductOpportunity::INTENT_LOW|null
     */
    public function classify(string $term): ?string
    {
        $termLower = mb_strtolower($term);

        // 1. Padrões de LOW intent (mais específicos)
        if ($this->hasKeywords($termLower, $this->lowIntentKeywords)) {
            return ProductOpportunity::INTENT_LOW;
        }

        // 2. Padrões de MEDIUM intent (meio termo)
        // "top 10 smartphones" → MEDIUM (não HIGH, mesmo com "smartphones")
        // "como escolher TV" → MEDIUM (não LOW, mesmo com "como")
        if ($this->hasKeywords($termLower, $this->mediumIntentKeywords)) {
            return ProductOpportunity::INTENT_MEDIUM;
        }

        // 3. Padrões de HIGH intent (menos específicos)
        if ($this->hasKeywords($termLower, $this->highIntentKeywords)) {
            return ProductOpportunity::INTENT_HIGH;
        }

        return null;
    }

    /**
     * Retorna score numérico baseado na intenção (para cálculo de oportunidade)
     */
    public function scoreByIntent(?string $intent): float
    {
        return match ($intent) {
            ProductOpportunity::INTENT_HIGH => 100.0,
            ProductOpportunity::INTENT_MEDIUM => 50.0,
            ProductOpportunity::INTENT_LOW => 25.0,
            default => 0.0,
        };
    }

    /**
     * Verifica se termo contém alguma palavra-chave
     */
    private function hasKeywords(string $term, array $keywords): bool
    {
        foreach ($keywords as $keyword) {
            if (str_contains($term, mb_strtolower($keyword))) {
                return true;
            }
        }

        return false;
    }
}
