<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessRecommendation extends Model
{
    public const TYPE_SALES_DROP = 'sales_drop';

    public const TYPE_KIT_OPPORTUNITY = 'kit_opportunity';

    public const TYPE_PRICE_OUTLIER = 'price_outlier';

    public const TYPE_IDEAL_TIMING = 'ideal_timing';

    protected $fillable = [
        'application_id',
        'seller_id',
        'type',
        'priority',
        'title',
        'message',
        'data',
        'generated_at',
    ];

    protected $casts = [
        'priority' => 'decimal:2',
        'data' => 'array',
        'generated_at' => 'datetime',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function scopeForSeller($query, int $sellerId)
    {
        return $query->where('seller_id', $sellerId);
    }
}
