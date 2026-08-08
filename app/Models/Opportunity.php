<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Opportunity extends Model
{
    public const TYPE_CROSS_SELL = 'cross_sell';

    public const TYPE_UP_SELL = 'up_sell';

    public const TYPE_WIN_BACK = 'win_back';

    public const TYPE_BUNDLE = 'bundle';

    protected $fillable = [
        'application_id',
        'type',
        'contact_id',
        'product_id',
        'related_product_id',
        'score',
        'potential_value',
        'reason',
        'detected_at',
    ];

    protected $casts = [
        'score' => 'decimal:2',
        'potential_value' => 'decimal:2',
        'detected_at' => 'datetime',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
