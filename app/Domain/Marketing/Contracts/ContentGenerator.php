<?php

namespace App\Domain\Marketing\Contracts;

interface ContentGenerator
{
    /**
     * @param  array{name: string, category: string, price?: float, description?: string}  $product
     */
    public function generateTitle(array $product): string;

    /**
     * @param  array{name: string, category: string, price?: float, description?: string}  $product
     */
    public function generateDescription(array $product): string;

    /**
     * @param  array{name: string, category: string, price?: float, description?: string}  $product
     * @return list<string>
     */
    public function generateSeoKeywords(array $product): array;

    /**
     * @param  array{name: string, category: string, price?: float, description?: string}  $product
     */
    public function generateSocialText(array $product, string $platform): string;

    /**
     * @param  array{name: string, category: string, price?: float, description?: string}  $product
     * @return list<string>
     */
    public function generateHashtags(array $product): array;

    /**
     * @param  array{name: string, category: string, price?: float, description?: string}  $product
     * @return array{subject: string, body: string}
     */
    public function generateEmailCampaign(array $product): array;
}
