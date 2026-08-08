<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductTrend extends Model
{
    public const DIRECTION_RISING = 'rising';

    public const DIRECTION_FALLING = 'falling';

    public const DIRECTION_STABLE = 'stable';

    protected $fillable = [
        'application_id',
        'product_id',
        'direction',
        'growth_rate',
        'current_views',
        'current_purchases',
        'current_revenue',
        'previous_views',
        'previous_purchases',
        'previous_revenue',
        'computed_at',
    ];

    protected $casts = [
        'growth_rate' => 'decimal:2',
        'current_revenue' => 'decimal:2',
        'previous_revenue' => 'decimal:2',
        'computed_at' => 'datetime',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function scopeRising($query)
    {
        return $query->where('direction', self::DIRECTION_RISING);
    }

    public function scopeFalling($query)
    {
        return $query->where('direction', self::DIRECTION_FALLING);
    }
}
