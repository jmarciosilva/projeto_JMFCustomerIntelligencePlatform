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
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'last_collected_at' => 'datetime',
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

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }
}
