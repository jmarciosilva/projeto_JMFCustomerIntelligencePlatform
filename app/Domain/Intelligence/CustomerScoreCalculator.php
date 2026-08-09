<?php

namespace App\Domain\Intelligence;

use App\Models\Contact;
use App\Models\Event;
use Carbon\Carbon;

class CustomerScoreCalculator
{
    public const MAX_SCORE = 100;

    public function calculate(Contact $contact): array
    {
        $now = now();
        $nineDaysAgo = $now->copy()->subDays(90);

        $events = Event::where('contact_id', $contact->id)
            ->whereBetween('occurred_at', [$nineDaysAgo, $now])
            ->get();

        if ($events->isEmpty()) {
            return [
                'customer_score' => 0,
                'recency_score' => 0,
                'frequency_score' => 0,
                'monetary_score' => 0,
            ];
        }

        $recencyScore = $this->calculateRecencyScore($contact, $now);
        $frequencyScore = $this->calculateFrequencyScore($events);
        $monetaryScore = $this->calculateMonetaryScore($events);

        $customerScore = (int) (($recencyScore + $frequencyScore + $monetaryScore) / 3);

        return [
            'customer_score' => min($customerScore, self::MAX_SCORE),
            'recency_score' => $recencyScore,
            'frequency_score' => $frequencyScore,
            'monetary_score' => $monetaryScore,
        ];
    }

    private function calculateRecencyScore(Contact $contact, Carbon $now): int
    {
        if (! $contact->last_seen_at) {
            return 0;
        }

        $daysSinceLastSeen = $contact->last_seen_at->diffInDays($now);

        return match (true) {
            $daysSinceLastSeen <= 7 => 100,
            $daysSinceLastSeen <= 14 => 80,
            $daysSinceLastSeen <= 30 => 60,
            $daysSinceLastSeen <= 60 => 40,
            $daysSinceLastSeen <= 90 => 20,
            default => 0,
        };
    }

    private function calculateFrequencyScore(mixed $events): int
    {
        $purchaseCount = $events->where('event_name', 'purchase.completed')->count();

        return match (true) {
            $purchaseCount >= 10 => 100,
            $purchaseCount >= 5 => 80,
            $purchaseCount >= 3 => 60,
            $purchaseCount >= 1 => 40,
            default => 20,
        };
    }

    private function calculateMonetaryScore(mixed $events): int
    {
        $totalValue = $events
            ->where('event_name', 'purchase.completed')
            ->sum(fn ($e) => $e->properties['total_value'] ?? 0);

        return match (true) {
            $totalValue >= 1000 => 100,
            $totalValue >= 500 => 80,
            $totalValue >= 200 => 60,
            $totalValue >= 50 => 40,
            $totalValue > 0 => 20,
            default => 0,
        };
    }
}
