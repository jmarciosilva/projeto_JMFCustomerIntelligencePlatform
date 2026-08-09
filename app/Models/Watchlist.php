<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Watchlist extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    public const FREQUENCY_DAILY = 'daily';

    public const FREQUENCY_WEEKLY = 'weekly';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'application_id',
        'name',
        'description',
        'keywords',
        'hashtags',
        'categories',
        'collection_frequency',
        'status',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'keywords' => 'array',
        'hashtags' => 'array',
        'categories' => 'array',
    ];

    /**
     * @return BelongsTo<Application, $this>
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    /**
     * @return HasMany<Trend, $this>
     */
    public function trends(): HasMany
    {
        return $this->hasMany(Trend::class);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }
}
