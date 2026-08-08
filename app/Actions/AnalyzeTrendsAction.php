<?php

namespace App\Actions;

use App\Domain\Intelligence\TrendAnalyzer;
use App\Models\Application;
use App\Models\ProductTrend;

class AnalyzeTrendsAction
{
    public function __construct(private TrendAnalyzer $analyzer) {}

    public function execute(?int $applicationId = null, int $periodDays = 7): int
    {
        $query = Application::query();

        if ($applicationId) {
            $query->where('id', $applicationId);
        }

        $updated = 0;

        foreach ($query->get() as $application) {
            $trends = $this->analyzer->analyze($application->id, $periodDays);

            foreach ($trends as $trend) {
                ProductTrend::updateOrCreate(
                    [
                        'application_id' => $application->id,
                        'product_id' => $trend['product_id'],
                    ],
                    [
                        'direction' => $trend['direction'],
                        'growth_rate' => $trend['growth_rate'],
                        'current_views' => $trend['current']['views'],
                        'current_purchases' => $trend['current']['purchases'],
                        'current_revenue' => $trend['current']['revenue'],
                        'previous_views' => $trend['previous']['views'],
                        'previous_purchases' => $trend['previous']['purchases'],
                        'previous_revenue' => $trend['previous']['revenue'],
                        'computed_at' => now(),
                    ]
                );

                $updated++;
            }
        }

        return $updated;
    }
}
