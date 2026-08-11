<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AffiliateLink extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_PAUSED = 'paused';

    public const STATUS_ARCHIVED = 'archived';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'campaign_id',
        'content_publication_id',
        'affiliate_product_id',
        'product_opportunity_id',
        'slug',
        'affiliate_url',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'clicks',
        'status',
    ];

    /**
     * @return BelongsTo<Campaign, $this>
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    /**
     * @return BelongsTo<ContentPublication, $this>
     */
    public function contentPublication(): BelongsTo
    {
        return $this->belongsTo(ContentPublication::class);
    }

    /**
     * @return BelongsTo<AffiliateProduct, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(AffiliateProduct::class, 'affiliate_product_id');
    }

    /**
     * @return HasMany<AffiliateClick, $this>
     */
    public function clicks(): HasMany
    {
        return $this->hasMany(AffiliateClick::class);
    }

    /**
     * @return BelongsTo<ProductOpportunity, $this>
     */
    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(ProductOpportunity::class, 'product_opportunity_id')->nullable();
    }

    /**
     * @return HasMany<AffiliateConversion, $this>
     */
    public function conversions(): HasMany
    {
        return $this->hasMany(AffiliateConversion::class);
    }

    /**
     * Gera URL de redirecionamento com UTMs
     */
    public function getRedirectUrl(): string
    {
        $params = [];

        if ($this->utm_source) {
            $params['utm_source'] = $this->utm_source;
        }
        if ($this->utm_medium) {
            $params['utm_medium'] = $this->utm_medium;
        }
        if ($this->utm_campaign) {
            $params['utm_campaign'] = $this->utm_campaign;
        }

        if (empty($params)) {
            return $this->affiliate_url;
        }

        $separator = str_contains($this->affiliate_url, '?') ? '&' : '?';

        return $this->affiliate_url . $separator . http_build_query($params);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }
}
