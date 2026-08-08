<?php

namespace App\Domain\Intelligence;

use App\Models\Application;
use App\Models\Contact;
use App\Models\Opportunity;
use App\Models\ProductAffinity;
use Illuminate\Support\Collection;

class OpportunityDetector
{
    private const CROSS_SELL_MIN_CO_OCCURRENCES = 3;

    private const BUNDLE_MIN_CO_OCCURRENCES = 8;

    private const UP_SELL_MIN_CUSTOMER_SCORE = 60;

    public function detectCrossSell(int $applicationId): Collection
    {
        return $this->affinityOpportunities(
            $applicationId,
            self::CROSS_SELL_MIN_CO_OCCURRENCES,
            self::BUNDLE_MIN_CO_OCCURRENCES, // exclusive upper bound: bundle handles >= this
            Opportunity::TYPE_CROSS_SELL,
            'Produtos frequentemente vistos/comprados juntos'
        );
    }

    public function detectBundles(int $applicationId): Collection
    {
        return $this->affinityOpportunities(
            $applicationId,
            self::BUNDLE_MIN_CO_OCCURRENCES,
            null,
            Opportunity::TYPE_BUNDLE,
            'Forte afinidade — candidato a kit/combo promocional'
        );
    }

    public function detectUpSell(int $applicationId): Collection
    {
        $tenantId = Application::findOrFail($applicationId)->tenant_id;

        return Contact::where('tenant_id', $tenantId)
            ->whereIn('segment', ['vip', 'converted'])
            ->where('customer_score', '>=', self::UP_SELL_MIN_CUSTOMER_SCORE)
            ->get()
            ->map(function (Contact $contact) {
                $totalValue = $this->totalPurchaseValue($contact);

                return [
                    'contact_id' => $contact->id,
                    'product_id' => null,
                    'related_product_id' => null,
                    'score' => min(100, $contact->customer_score),
                    'potential_value' => round($totalValue * 0.3, 2),
                    'reason' => 'Cliente engajado com histórico de compras — candidato a upgrade/produto premium',
                ];
            });
    }

    public function detectWinBack(int $applicationId): Collection
    {
        $tenantId = Application::findOrFail($applicationId)->tenant_id;

        return Contact::where('tenant_id', $tenantId)
            ->where('segment', 'inactive')
            ->get()
            ->map(function (Contact $contact) {
                $totalValue = $this->totalPurchaseValue($contact);

                return [$contact, $totalValue];
            })
            ->filter(fn ($pair) => $pair[1] > 0)
            ->map(function ($pair) {
                [$contact, $totalValue] = $pair;
                $daysSinceLastSeen = $contact->last_seen_at ? $contact->last_seen_at->diffInDays(now()) : 999;

                return [
                    'contact_id' => $contact->id,
                    'product_id' => null,
                    'related_product_id' => null,
                    'score' => max(0, 100 - $daysSinceLastSeen),
                    'potential_value' => round($totalValue, 2),
                    'reason' => "Cliente inativo há {$daysSinceLastSeen} dias com histórico de compras",
                ];
            })
            ->values();
    }

    private function affinityOpportunities(
        int $applicationId,
        int $minCoOccurrences,
        ?int $maxCoOccurrencesExclusive,
        string $type,
        string $reason
    ): Collection {
        $query = ProductAffinity::where('application_id', $applicationId)
            ->where('co_occurrences', '>=', $minCoOccurrences);

        if ($maxCoOccurrencesExclusive !== null) {
            $query->where('co_occurrences', '<', $maxCoOccurrencesExclusive);
        }

        return $query->get()->map(fn (ProductAffinity $affinity) => [
            'product_id' => $affinity->subject_id_a,
            'related_product_id' => $affinity->subject_id_b,
            'score' => min(100, $affinity->co_occurrences * 10),
            'potential_value' => null,
            'reason' => $reason,
        ]);
    }

    private function totalPurchaseValue(Contact $contact): float
    {
        return (float) $contact->events()
            ->where('event_name', 'purchase.completed')
            ->get()
            ->sum(fn ($event) => $event->properties['total_value'] ?? 0);
    }
}
