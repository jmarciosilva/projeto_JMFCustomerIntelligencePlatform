<?php

namespace App\Actions;

use App\Domain\Intelligence\OpportunityDetector;
use App\Models\Application;
use App\Models\Opportunity;

class DetectOpportunitiesAction
{
    public function __construct(private OpportunityDetector $detector) {}

    public function execute(?int $applicationId = null): int
    {
        $query = Application::query();

        if ($applicationId) {
            $query->where('id', $applicationId);
        }

        $detected = 0;

        foreach ($query->get() as $application) {
            // Clear stale opportunities before recomputing, so resolved/expired ones don't linger
            Opportunity::where('application_id', $application->id)->delete();

            $detected += $this->persist($application->id, Opportunity::TYPE_CROSS_SELL, $this->detector->detectCrossSell($application->id));
            $detected += $this->persist($application->id, Opportunity::TYPE_BUNDLE, $this->detector->detectBundles($application->id));
            $detected += $this->persist($application->id, Opportunity::TYPE_UP_SELL, $this->detector->detectUpSell($application->id));
            $detected += $this->persist($application->id, Opportunity::TYPE_WIN_BACK, $this->detector->detectWinBack($application->id));
        }

        return $detected;
    }

    private function persist(int $applicationId, string $type, $opportunities): int
    {
        $count = 0;

        foreach ($opportunities as $opportunity) {
            Opportunity::create(array_merge($opportunity, [
                'application_id' => $applicationId,
                'type' => $type,
                'detected_at' => now(),
            ]));

            $count++;
        }

        return $count;
    }
}
