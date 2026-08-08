<?php

namespace App\Actions;

use App\Domain\Intelligence\ForecastEngine;
use App\Models\Application;
use App\Models\MarketplaceMetric;
use App\Models\SalesForecast;

class ForecastSalesAction
{
    private const HORIZONS = [7, 30];

    public function __construct(private ForecastEngine $engine) {}

    public function execute(?int $applicationId = null): int
    {
        $query = Application::query();

        if ($applicationId) {
            $query->where('id', $applicationId);
        }

        $created = 0;

        foreach ($query->get() as $application) {
            foreach (self::HORIZONS as $horizonDays) {
                $created += $this->storeForecast($application->id, null, $horizonDays);
            }

            $sellerIds = MarketplaceMetric::where('application_id', $application->id)
                ->whereNotNull('seller_id')
                ->distinct()
                ->pluck('seller_id');

            foreach ($sellerIds as $sellerId) {
                foreach (self::HORIZONS as $horizonDays) {
                    $created += $this->storeForecast($application->id, $sellerId, $horizonDays);
                }
            }
        }

        return $created;
    }

    private function storeForecast(int $applicationId, ?int $sellerId, int $horizonDays): int
    {
        $forecast = $this->engine->forecast($applicationId, $horizonDays, $sellerId);

        SalesForecast::updateOrCreate(
            [
                'application_id' => $applicationId,
                'seller_id' => $sellerId,
                'forecast_date' => now()->toDateString(),
                'horizon_days' => $horizonDays,
            ],
            [
                'predicted_revenue' => $forecast['predicted_revenue'],
                'predicted_purchases' => $forecast['predicted_purchases'],
                'confidence' => $forecast['confidence'],
                'method' => $forecast['method'],
                'computed_at' => now(),
            ]
        );

        return 1;
    }
}
