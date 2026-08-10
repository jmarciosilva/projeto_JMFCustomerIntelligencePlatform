<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Trend extends Model
{
    use HasFactory;

    public const TYPE_KEYWORD = 'keyword';

    public const TYPE_HASHTAG = 'hashtag';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'application_id',
        'watchlist_id',
        'term',
        'type',
        'status',
        'last_collected_at',
        'trend_score',
        'trend_score_breakdown',
        'trend_score_computed_at',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'last_collected_at' => 'datetime',
        'trend_score' => 'decimal:2',
        'trend_score_breakdown' => 'array',
        'trend_score_computed_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<Application, $this>
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    /**
     * @return BelongsTo<Watchlist, $this>
     */
    public function watchlist(): BelongsTo
    {
        return $this->belongsTo(Watchlist::class);
    }

    /**
     * @return HasMany<TrendSnapshot, $this>
     */
    public function snapshots(): HasMany
    {
        return $this->hasMany(TrendSnapshot::class);
    }

    /**
     * @return HasMany<TrendProductMatch, $this>
     */
    public function productMatches(): HasMany
    {
        return $this->hasMany(TrendProductMatch::class);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }
}
