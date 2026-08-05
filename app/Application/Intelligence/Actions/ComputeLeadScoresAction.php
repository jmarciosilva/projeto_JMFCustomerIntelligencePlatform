<?php

namespace App\Application\Intelligence\Actions;

use App\Domain\Intelligence\LeadScoreRules;
use App\Models\Contact;
use App\Models\Event;

class ComputeLeadScoresAction
{
    public function handle(): void
    {
        Contact::query()->chunkById(100, function ($contacts): void {
            foreach ($contacts as $contact) {
                $this->computeFor($contact);
            }
        });
    }

    private function computeFor(Contact $contact): void
    {
        $visitors = $contact->visitors()->get(['application_id', 'visitor_id']);

        if ($visitors->isEmpty()) {
            $contact->forceFill(['lead_score' => 0, 'lead_score_computed_at' => now()])->save();

            return;
        }

        // Soma eventos de todos os Visitors do contato, cruzando aplicações
        // dentro do mesmo tenant — Contact é único por tenant (Fase 05)
        // justamente para unificar a mesma pessoa entre produtos da JMF System.
        $eventCounts = Event::query()
            ->where(function ($query) use ($visitors): void {
                foreach ($visitors as $visitor) {
                    $query->orWhere(function ($inner) use ($visitor): void {
                        $inner->where('application_id', $visitor->application_id)
                            ->where('visitor_id', $visitor->visitor_id);
                    });
                }
            })
            ->selectRaw('event_name, COUNT(*) AS total')
            ->groupBy('event_name')
            ->pluck('total', 'event_name');

        $score = 0;

        foreach ($eventCounts as $eventName => $count) {
            $score += LeadScoreRules::pointsFor($eventName) * (int) $count;
        }

        $contact->forceFill([
            'lead_score' => $score,
            'lead_score_computed_at' => now(),
        ])->save();
    }
}
