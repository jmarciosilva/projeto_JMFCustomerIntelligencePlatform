<?php

namespace App\Actions;

use App\Domain\Marketing\Contracts\ContentGenerator;
use App\Models\MarketingContent;

class GenerateEmailCampaignAction
{
    public function __construct(private ContentGenerator $generator) {}

    /**
     * @param  array{name: string, category: string, price?: float, description?: string}  $product
     */
    public function execute(int $applicationId, string $subjectType, int|string $subjectId, array $product): MarketingContent
    {
        $campaign = $this->generator->generateEmailCampaign($product);

        return MarketingContent::create([
            'application_id' => $applicationId,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'type' => MarketingContent::TYPE_EMAIL_CAMPAIGN,
            'content' => $campaign['body'],
            'metadata' => ['subject' => $campaign['subject']],
            'status' => MarketingContent::STATUS_DRAFT,
            'generator' => config('marketing.driver'),
            'generated_at' => now(),
        ]);
    }
}
