<?php

namespace App\Application\Intelligence\Actions;

use App\Models\Application;
use App\Models\Event;
use App\Models\ProductAffinity;

class GetRecommendationsAction
{
    /**
     * @return list<array{subject_id: string, subject_type: string, score: int, source: 'affinity'|'popularity'}>
     */
    public function handle(Application $application, string $subjectType, string $subjectId, int $limit = 5): array
    {
        $recommendations = ProductAffinity::query()
            ->where('application_id', $application->id)
            ->where('subject_type', $subjectType)
            ->where(function ($query) use ($subjectId): void {
                $query->where('subject_id_a', $subjectId)
                    ->orWhere('subject_id_b', $subjectId);
            })
            ->orderByDesc('co_occurrences')
            ->limit($limit)
            ->get()
            ->map(fn (ProductAffinity $affinity) => [
                'subject_id' => $affinity->subject_id_a === $subjectId ? $affinity->subject_id_b : $affinity->subject_id_a,
                'subject_type' => $affinity->subject_type,
                'score' => $affinity->co_occurrences,
                'source' => 'affinity',
            ])
            ->values();

        if ($recommendations->count() >= $limit) {
            return $recommendations->all();
        }

        // Sem afinidade suficiente (cold start): completa com popularidade —
        // subjects do mesmo tipo mais vistos, direto em `events`, sem depender
        // de um event_name específico de "visualização".
        $excludeIds = $recommendations->pluck('subject_id')->push($subjectId)->all();
        $remaining = $limit - $recommendations->count();

        $popular = Event::query()
            ->where('application_id', $application->id)
            ->where('subject_type', $subjectType)
            ->whereNotNull('subject_id')
            ->whereNotIn('subject_id', $excludeIds)
            ->selectRaw('subject_id, COUNT(*) AS total')
            ->groupBy('subject_id')
            ->orderByDesc('total')
            ->limit($remaining)
            ->get()
            ->map(fn ($row) => [
                'subject_id' => $row['subject_id'],
                'subject_type' => $subjectType,
                'score' => (int) $row['total'],
                'source' => 'popularity',
            ]);

        return $recommendations->concat($popular)->values()->all();
    }
}
