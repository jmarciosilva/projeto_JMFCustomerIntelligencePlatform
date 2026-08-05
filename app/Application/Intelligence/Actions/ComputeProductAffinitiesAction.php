<?php

namespace App\Application\Intelligence\Actions;

use App\Models\Application;
use App\Models\Event;
use App\Models\ProductAffinity;

class ComputeProductAffinitiesAction
{
    private const LOOKBACK_DAYS = 90;

    public function handle(Application $application): void
    {
        $rows = Event::query()
            ->where('application_id', $application->id)
            ->whereNotNull('subject_type')
            ->whereNotNull('subject_id')
            ->where('occurred_at', '>=', now()->subDays(self::LOOKBACK_DAYS))
            ->get(['visitor_id', 'subject_type', 'subject_id']);

        $subjectsByVisitor = [];

        foreach ($rows as $row) {
            $subjectsByVisitor[$row->visitor_id][$row->subject_type][$row->subject_id] = true;
        }

        $pairCounts = [];

        foreach ($subjectsByVisitor as $subjectTypes) {
            foreach ($subjectTypes as $subjectType => $subjectIds) {
                $ids = array_map('strval', array_keys($subjectIds));
                sort($ids, SORT_STRING);
                $count = count($ids);

                for ($i = 0; $i < $count; $i++) {
                    for ($j = $i + 1; $j < $count; $j++) {
                        $pairCounts[$subjectType][$ids[$i]][$ids[$j]] =
                            ($pairCounts[$subjectType][$ids[$i]][$ids[$j]] ?? 0) + 1;
                    }
                }
            }
        }

        foreach ($pairCounts as $subjectType => $byA) {
            foreach ($byA as $subjectIdA => $byB) {
                foreach ($byB as $subjectIdB => $coOccurrences) {
                    ProductAffinity::query()->updateOrCreate(
                        [
                            'application_id' => $application->id,
                            'subject_type' => $subjectType,
                            'subject_id_a' => (string) $subjectIdA,
                            'subject_id_b' => (string) $subjectIdB,
                        ],
                        [
                            'co_occurrences' => $coOccurrences,
                            'computed_at' => now(),
                        ]
                    );
                }
            }
        }
    }
}
