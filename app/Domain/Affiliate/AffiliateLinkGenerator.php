<?php

namespace App\Domain\Affiliate;

use App\Models\AffiliateLink;
use Illuminate\Support\Str;

class AffiliateLinkGenerator
{
    /**
     * Gera um slug único para um link de afiliado
     */
    public function generateSlug(string $baseText, ?int $maxLength = 50): string
    {
        $base = Str::slug($baseText, '-');

        // Truncar se necessário
        if (strlen($base) > $maxLength) {
            $base = substr($base, 0, $maxLength);
        }

        $slug = $base;
        $counter = 1;

        // Se slug já existe, adicionar sufixo numérico
        while (AffiliateLink::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    /**
     * Cria uma URL de redirecionamento com UTMs
     */
    public function buildRedirectUrl(
        string $affiliateUrl,
        ?string $utmSource = null,
        ?string $utmMedium = null,
        ?string $utmCampaign = null
    ): string {
        $params = [];

        if ($utmSource) {
            $params['utm_source'] = $utmSource;
        }
        if ($utmMedium) {
            $params['utm_medium'] = $utmMedium;
        }
        if ($utmCampaign) {
            $params['utm_campaign'] = $utmCampaign;
        }

        if (empty($params)) {
            return $affiliateUrl;
        }

        $separator = str_contains($affiliateUrl, '?') ? '&' : '?';

        return $affiliateUrl.$separator.http_build_query($params);
    }
}
