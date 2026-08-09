<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrendSnapshot extends Model
{
    use HasFactory;

    public const SOURCE_MANUAL = 'manual';

    public const SOURCE_INTERNAL_BEHAVIOR = 'internal_behavior';

    public const SOURCE_INSTAGRAM = 'instagram';

    public const SOURCE_GOOGLE_TRENDS = 'google_trends';

    public const SOURCE_YOUTUBE = 'youtube';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'trend_id',
        'source',
        'score',
        'mentions',
        'engagement',
        'velocity',
        'metadata',
        'collected_at',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'score' => 'decimal:2',
        'mentions' => 'integer',
        'engagement' => 'integer',
        'velocity' => 'decimal:2',
        'metadata' => 'array',
        'collected_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<Trend, $this>
     */
    public function trend(): BelongsTo
    {
        return $this->belongsTo(Trend::class);
    }

    public function scopeSince($query, \DateTimeInterface $date)
    {
        return $query->where('collected_at', '>=', $date);
    }
}
