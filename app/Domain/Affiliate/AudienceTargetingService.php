<?php

namespace App\Domain\Affiliate;

use App\Domain\Affiliate\Enums\PurchaseIntentLabel;
use App\Models\AudienceSegment;
use App\Models\ProductOpportunity;
use Illuminate\Support\Collection;

class AudienceTargetingService
{
    /**
     * Determina os segmentos de público mais adequados para uma oportunidade
     *
     * Baseado em regras determinísticas:
     * - HIGH intent → Profissionais, Pais, Health-conscious
     * - MEDIUM intent → Todos (genérico)
     * - LOW intent → Curiosos/Browsing
     * - Categoria do produto → filtra segmentos por interest
     */
    public function determineAudiencesForOpportunity(ProductOpportunity $opportunity): Collection
    {
        $audiences = collect();

        if (! $opportunity->affiliate_product_id) {
            return $audiences;
        }

        $product = $opportunity->affiliateProduct;
        if (! $product) {
            return $audiences;
        }

        $intent = $opportunity->purchase_intent_label?->value;
        $category = strtolower($product->category ?? '');

        // Mapa de categorias para segmentos de interesse
        $categorySegmentMap = [
            'tecnologia' => 'Tecnologia Entusiastas',
            'tech' => 'Tecnologia Entusiastas',
            'eletrônicos' => 'Tecnologia Entusiastas',
            'saúde' => 'Saúde & Bem-estar',
            'beleza' => 'Saúde & Bem-estar',
            'bem-estar' => 'Saúde & Bem-estar',
            'fitness' => 'Saúde & Bem-estar',
            'moda' => 'Jovens Adultos',
            'roupas' => 'Jovens Adultos',
            'infantil' => 'Pais',
            'bebê' => 'Pais',
            'criança' => 'Pais',
        ];

        // Determina segmentos baseado em intent
        $intentSegments = $this->getSegmentsByIntent($intent);

        // Refina por categoria se aplicável
        $matches = array_filter($categorySegmentMap, fn ($cat) => str_contains($category, $cat));
        if ($matches) {
            $key = array_key_first($matches);
            if ($key !== null) {
                $audiences->push($categorySegmentMap[$key]);
            }
        }

        // Adiciona segmentos por intent se ainda não estão inclusos
        foreach ($intentSegments as $segmentName) {
            if (! $audiences->contains($segmentName)) {
                $audiences->push($segmentName);
            }
        }

        // Recupera os modelos reais do banco
        return AudienceSegment::whereIn('name', $audiences->unique())
            ->active()
            ->get();
    }

    /**
     * Retorna nomes de segmentos baseado em intent
     */
    private function getSegmentsByIntent(?string $intent): Collection
    {
        return match ($intent) {
            PurchaseIntentLabel::HIGH->value => collect([
                'Profissionais',
                'Pais',
                'Saúde & Bem-estar',
            ]),
            PurchaseIntentLabel::MEDIUM->value => collect([
                'Jovens Adultos',
                'Profissionais',
            ]),
            PurchaseIntentLabel::LOW->value => collect([
                'Curiosos & Browsing',
            ]),
            default => collect(),
        };
    }
}
