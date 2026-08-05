<?php

namespace App\Application\Analytics\Actions;

use App\Domain\Analytics\DateRange;
use App\Models\Application;
use App\Models\Event;

class GetTopSubjectsAction
{
    /**
     * @return list<array{subject_id: string, subject_type: string, total: int}>
     */
    public function handle(
        Application $application,
        DateRange $range,
        string $eventName,
        ?string $subjectType = null,
        int $limit = 10,
    ): array {
        return Event::query()
            ->where('application_id', $application->id)
            ->where('event_name', $eventName)
            ->when($subjectType, fn ($query) => $query->where('subject_type', $subjectType))
            ->whereNotNull('subject_id')
            ->whereBetween('occurred_at', [$range->from, $range->to])
            ->selectRaw('subject_id, subject_type, COUNT(*) AS total')
            ->groupBy('subject_id', 'subject_type')
            ->orderByDesc('total')
            ->limit($limit)
            ->get()
            ->map(fn ($row) => [
                'subject_id' => $row['subject_id'],
                'subject_type' => $row['subject_type'],
                'total' => (int) $row['total'],
            ])
            ->all();
    }
}
