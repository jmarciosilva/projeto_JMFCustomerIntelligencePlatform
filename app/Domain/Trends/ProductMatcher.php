<?php

namespace App\Domain\Trends;

use App\Models\AffiliateProduct;
use App\Models\Trend;
use App\Models\TrendProductMatch;
use Illuminate\Support\Collection;

class ProductMatcher
{
    /**
     * Encontra produtos que combinam com um trend baseado em:
     * - Similaridade de texto (keyword/nome/descrição): 40%
     * - Matching de categoria: 35%
     * - Matching de marca: 25%
     *
     * Retorna collection de TrendProductMatch com score calculado
     */
    public function match(Trend $trend): Collection
    {
        $products = AffiliateProduct::where('application_id', $trend->application_id)
            ->with('affiliateProgram')
            ->get();

        $matches = [];

        foreach ($products as $product) {
            $score = $this->calculateMatchScore($trend, $product);

            if ($score > 0) {
                $matches[] = [
                    'trend_id' => $trend->id,
                    'affiliate_product_id' => $product->id,
                    'match_score' => $score,
                    'match_breakdown' => $this->getMatchBreakdown($trend, $product),
                ];
            }
        }

        // Ordenar por score decrescente
        usort($matches, fn ($a, $b) => $b['match_score'] <=> $a['match_score']);

        return collect($matches);
    }

    /**
     * Calcula score de matching entre trend e produto
     */
    private function calculateMatchScore(Trend $trend, AffiliateProduct $product): float
    {
        $keywordScore = $this->calculateKeywordScore($trend->term, $product);
        $categoryScore = $this->calculateCategoryScore($trend->term, $product);
        $brandScore = $this->calculateBrandScore($trend->term, $product);

        // Ponderação: keyword 40%, categoria 35%, marca 25%
        return ($keywordScore * 0.40) + ($categoryScore * 0.35) + ($brandScore * 0.25);
    }

    /**
     * Score de similaridade entre termo do trend e nome/descrição do produto
     * Usa levenshtein (distância mínima de edição)
     */
    private function calculateKeywordScore(string $term, AffiliateProduct $product): float
    {
        $termWords = str_word_count(mb_strtolower($term), 1);
        $productName = mb_strtolower($product->name);
        $productDescription = mb_strtolower($product->description ?? '');
        $combined = "$productName $productDescription";

        $maxScore = 0;

        foreach ($termWords as $word) {
            if (empty(trim($word)) || strlen($word) < 2) {
                continue;
            }

            // Score por matching exato (melhor)
            if (str_contains($combined, $word)) {
                $maxScore = max($maxScore, 100);
                continue;
            }

            // Score por similaridade (levenshtein)
            $combinedWords = str_word_count($combined, 1);
            foreach ($combinedWords as $productWord) {
                if (strlen($productWord) < 2) {
                    continue;
                }

                $distance = levenshtein($word, $productWord);
                $maxLen = max(strlen($word), strlen($productWord));

                if ($maxLen > 0) {
                    $similarity = (1 - ($distance / $maxLen)) * 100;
                    // Considerar similarity >= 70% como match relevante
                    if ($similarity >= 70) {
                        $maxScore = max($maxScore, $similarity);
                    }
                }
            }
        }

        return $maxScore;
    }

    /**
     * Score de matching de categoria
     */
    private function calculateCategoryScore(string $term, AffiliateProduct $product): float
    {
        if (empty($product->category)) {
            return 0;
        }

        $termLower = mb_strtolower($term);
        $categoryLower = mb_strtolower($product->category);

        // Exact match na categoria
        if ($categoryLower === $termLower || str_contains($termLower, $categoryLower) || str_contains($categoryLower, $termLower)) {
            return 100;
        }

        // Similarity parcial
        $distance = levenshtein($termLower, $categoryLower);
        $maxLen = max(strlen($termLower), strlen($categoryLower));

        if ($maxLen > 0) {
            $similarity = (1 - ($distance / $maxLen)) * 100;
            return $similarity >= 60 ? $similarity : 0;
        }

        return 0;
    }

    /**
     * Score de matching de marca
     */
    private function calculateBrandScore(string $term, AffiliateProduct $product): float
    {
        if (empty($product->brand)) {
            return 0;
        }

        $termLower = mb_strtolower($term);
        $brandLower = mb_strtolower($product->brand);

        // Exact match na marca
        if ($brandLower === $termLower || str_contains($termLower, $brandLower) || str_contains($brandLower, $termLower)) {
            return 100;
        }

        // Similarity parcial
        $distance = levenshtein($termLower, $brandLower);
        $maxLen = max(strlen($termLower), strlen($brandLower));

        if ($maxLen > 0) {
            $similarity = (1 - ($distance / $maxLen)) * 100;
            return $similarity >= 60 ? $similarity : 0;
        }

        return 0;
    }

    /**
     * Retorna breakdown do score (quais fatores contribuiram)
     */
    private function getMatchBreakdown(Trend $trend, AffiliateProduct $product): array
    {
        $keywordScore = $this->calculateKeywordScore($trend->term, $product);
        $categoryScore = $this->calculateCategoryScore($trend->term, $product);
        $brandScore = $this->calculateBrandScore($trend->term, $product);

        return [
            'keyword' => round($keywordScore, 2),
            'category' => round($categoryScore, 2),
            'brand' => round($brandScore, 2),
        ];
    }

    /**
     * Persiste os matches na tabela
     */
    public function persistMatches(Trend $trend, Collection $matches): int
    {
        $count = 0;

        foreach ($matches as $matchData) {
            TrendProductMatch::updateOrCreate(
                [
                    'trend_id' => $matchData['trend_id'],
                    'affiliate_product_id' => $matchData['affiliate_product_id'],
                ],
                [
                    'match_score' => $matchData['match_score'],
                    'match_breakdown' => $matchData['match_breakdown'],
                ]
            );
            $count++;
        }

        return $count;
    }
}
