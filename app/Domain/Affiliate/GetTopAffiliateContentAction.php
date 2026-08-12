<?php

namespace App\Domain\Affiliate;

use App\Models\AffiliateLink;
use App\Models\Application;
use App\Models\ContentPublication;
use Carbon\Carbon;

class GetTopAffiliateContentAction
{
    public function execute(Application $application, ?Carbon $startDate = null, ?Carbon $endDate = null, int $limit = 10): array
    {
        $startDate = $startDate ?? now()->startOfMonth();
        $endDate = $endDate ?? now()->endOfDay();

        $content = ContentPublication::where('application_id', $application->id)
            ->with('campaign')
            ->get()
            ->map(function ($pub) {
                $clicks = AffiliateLink::where('application_id', $pub->application_id)
                    ->where('campaign_id', $pub->campaign_id)
                    ->sum('clicks');

                return [
                    'content_id' => $pub->id,
                    'title' => $pub->title,
                    'platform' => $pub->platform,
                    'campaign_name' => $pub->campaign->name ?? 'Sem campanha',
                    'clicks' => $clicks,
                    'published_at' => $pub->published_at?->toDateString(),
                ];
            })
            ->sortByDesc('clicks')
            ->take($limit)
            ->values()
            ->toArray();

        return $content;
    }
}
