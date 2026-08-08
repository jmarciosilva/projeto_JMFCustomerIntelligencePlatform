<?php

namespace App\Actions;

use App\Domain\Marketing\Contracts\ContentGenerator;
use App\Models\MarketingContent;

class GenerateProductContentAction
{
    public function __construct(private ContentGenerator $generator) {}

    /**
     * @param  array{name: string, category: string, price?: float, description?: string}  $product
     * @return array<int, MarketingContent>
     */
    public function execute(int $applicationId, string $subjectType, int|string $subjectId, array $product): array
    {
        $driverName = config('marketing.driver');

        $title = $this->generator->generateTitle($product);
        $description = $this->generator->generateDescription($product);
        $keywords = $this->generator->generateSeoKeywords($product);

        $records = [
            [
                'type' => MarketingContent::TYPE_TITLE,
                'content' => $title,
                'metadata' => null,
            ],
            [
                'type' => MarketingContent::TYPE_DESCRIPTION,
                'content' => $description,
                'metadata' => null,
            ],
            [
                'type' => MarketingContent::TYPE_SEO_KEYWORDS,
                'content' => implode(', ', $keywords),
                'metadata' => ['keywords' => $keywords],
            ],
        ];

        return array_map(fn ($record) => MarketingContent::create(array_merge($record, [
            'application_id' => $applicationId,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'status' => MarketingContent::STATUS_DRAFT,
            'generator' => $driverName,
            'generated_at' => now(),
        ])), $records);
    }
}
