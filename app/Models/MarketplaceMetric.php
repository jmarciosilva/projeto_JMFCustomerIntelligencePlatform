<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketplaceMetric extends Model
{
    protected $fillable = [
        'tenant_id',
        'application_id',
        'seller_id',
        'product_id',
        'date',
        'product_views',
        'product_favorites',
        'cart_adds',
        'cart_views',
        'cart_abandonments',
        'checkout_starts',
        'purchases',
        'reviews',
        'unique_visitors',
        'unique_buyers',
        'revenue',
        'avg_order_value',
        'conversion_rate',
    ];

    protected $casts = [
        'date' => 'date',
        'revenue' => 'decimal:2',
        'avg_order_value' => 'decimal:2',
        'conversion_rate' => 'decimal:2',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function scopeForSeller($query, int $sellerId)
    {
        return $query->where('seller_id', $sellerId);
    }

    public function scopeForProduct($query, int $productId)
    {
        return $query->where('product_id', $productId);
    }

    public function scopeForDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }
}
