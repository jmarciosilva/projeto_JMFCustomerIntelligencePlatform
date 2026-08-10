<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductPerformanceScore extends Model
{
    protected $fillable = [
        'application_id',
        'affiliate_product_id',
        'total_clicks',
        'total_conversions',
        'total_sales',
        'total_commission',
        'total_revenue',
        'ctr_score',
        'conversion_score',
        'sales_score',
        'recurrence_score',
        'performance_score',
        'calculated_at',
    ];

    protected $casts = [
        'calculated_at' => 'datetime',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(AffiliateProduct::class, 'affiliate_product_id');
    }
}
