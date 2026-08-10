<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AffiliateClick extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'affiliate_link_id',
        'visitor_id',
        'source',
        'medium',
        'referer',
        'user_agent',
        'ip_hash',
        'clicked_at',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'clicked_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<AffiliateLink, $this>
     */
    public function affiliateLink(): BelongsTo
    {
        return $this->belongsTo(AffiliateLink::class);
    }
}
