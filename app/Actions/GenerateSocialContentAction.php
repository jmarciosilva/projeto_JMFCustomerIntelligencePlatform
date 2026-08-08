<?php

namespace App\Actions;

use App\Domain\Marketing\Contracts\ContentGenerator;
use App\Models\MarketingContent;

class GenerateSocialContentAction
{
    private const PLATFORMS = [
        'instagram' => MarketingContent::TYPE_SOCIAL_INSTAGRAM,
        'facebook' => MarketingContent::TYPE_SOCIAL_FACEBOOK,
        'whatsapp' => MarketingContent::TYPE_SOCIAL_WHATSAPP,
    ];

    public function __construct(private ContentGenerator $generator) {}

    /**
     * @param  array{name: string, category: string, price?: float, description?: string}  $product
     * @return array<int, MarketingContent>
     */
    public function execute(int $applicationId, string $subjectType, int|string $subjectId, array $product): array
    {
        $driverName = config('marketing.driver');
        $hashtags = $this->generator->generateHashtags($product);

        $records = [];

        foreach (self::PLATFORMS as $platform => $type) {
            $records[] = MarketingContent::create([
                'application_id' => $applicationId,
                'subject_type' => $subjectType,
                'subject_id' => $subjectId,
                'type' => $type,
                'content' => $this->generator->generateSocialText($product, $platform),
                'metadata' => ['hashtags' => $hashtags, 'platform' => $platform],
                'status' => MarketingContent::STATUS_DRAFT,
                'generator' => $driverName,
                'generated_at' => now(),
            ]);
        }

        return $records;
    }
}
