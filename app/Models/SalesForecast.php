<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesForecast extends Model
{
    public const CONFIDENCE_LOW = 'low';

    public const CONFIDENCE_MEDIUM = 'medium';

    public const CONFIDENCE_HIGH = 'high';

    protected $fillable = [
        'application_id',
        'seller_id',
        'forecast_date',
        'horizon_days',
        'predicted_revenue',
        'predicted_purchases',
        'confidence',
        'method',
        'computed_at',
    ];

    protected $casts = [
        'forecast_date' => 'date',
        'predicted_revenue' => 'decimal:2',
        'computed_at' => 'datetime',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }
}
