<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContentPublication extends Model
{
    use HasFactory;

    public const TYPE_BLOG_POST = 'blog_post';

    public const TYPE_SOCIAL_MEDIA = 'social_media';

    public const TYPE_EMAIL = 'email';

    public const TYPE_VIDEO = 'video';

    public const TYPE_OTHER = 'other';

    public const PLATFORM_INSTAGRAM = 'instagram';

    public const PLATFORM_FACEBOOK = 'facebook';

    public const PLATFORM_WHATSAPP = 'whatsapp';

    public const PLATFORM_EMAIL = 'email';

    public const PLATFORM_WEBSITE = 'website';

    public const PLATFORM_OTHER = 'other';

    public const STATUS_DRAFT = 'draft';

    public const STATUS_PUBLISHED = 'published';

    public const STATUS_ARCHIVED = 'archived';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'campaign_id',
        'product_opportunity_id',
        'title',
        'description',
        'content_type',
        'platform',
        'published_at',
        'url',
        'status',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'published_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<Campaign, $this>
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    /**
     * @return BelongsTo<ProductOpportunity, $this>
     */
    public function productOpportunity(): BelongsTo
    {
        return $this->belongsTo(ProductOpportunity::class);
    }

    /**
     * @return HasMany<AffiliateLink, $this>
     */
    public function affiliateLinks(): HasMany
    {
        return $this->hasMany(AffiliateLink::class);
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }
}
