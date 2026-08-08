<?php

namespace App\Actions;

use App\Domain\Intelligence\BusinessAdvisor;
use App\Models\Application;
use App\Models\BusinessRecommendation;

class GenerateBusinessRecommendationsAction
{
    public function __construct(private BusinessAdvisor $advisor) {}

    public function execute(?int $applicationId = null): int
    {
        $query = Application::query();

        if ($applicationId) {
            $query->where('id', $applicationId);
        }

        $generated = 0;

        foreach ($query->get() as $application) {
            // Clear stale recommendations before recomputing, so resolved issues don't linger
            BusinessRecommendation::where('application_id', $application->id)->delete();

            foreach ($this->advisor->generateForApplication($application->id) as $recommendation) {
                BusinessRecommendation::create(array_merge($recommendation, [
                    'application_id' => $application->id,
                    'generated_at' => now(),
                ]));

                $generated++;
            }
        }

        return $generated;
    }
}
