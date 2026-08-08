<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketingContent extends Model
{
    public const TYPE_TITLE = 'title';

    public const TYPE_DESCRIPTION = 'description';

    public const TYPE_SEO_KEYWORDS = 'seo_keywords';

    public const TYPE_SOCIAL_INSTAGRAM = 'social_instagram';

    public const TYPE_SOCIAL_FACEBOOK = 'social_facebook';

    public const TYPE_SOCIAL_WHATSAPP = 'social_whatsapp';

    public const TYPE_EMAIL_CAMPAIGN = 'email_campaign';

    public const STATUS_DRAFT = 'draft';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'application_id',
        'subject_type',
        'subject_id',
        'type',
        'content',
        'metadata',
        'status',
        'generator',
        'generated_at',
        'reviewed_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'generated_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function scopeForSubject($query, string $subjectType, int|string $subjectId)
    {
        return $query->where('subject_type', $subjectType)->where('subject_id', $subjectId);
    }
}
