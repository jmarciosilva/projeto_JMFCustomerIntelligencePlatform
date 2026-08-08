<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerSegment extends Model
{
    protected $fillable = [
        'contact_id',
        'segment',
        'customer_score',
        'recency_score',
        'frequency_score',
        'monetary_score',
        'segmented_at',
    ];

    protected $casts = [
        'customer_score' => 'integer',
        'recency_score' => 'integer',
        'frequency_score' => 'integer',
        'monetary_score' => 'integer',
        'segmented_at' => 'datetime',
    ];

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }
}
