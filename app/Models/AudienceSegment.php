<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AudienceSegment extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'description',
        'age_range_approx',
        'interests',
        'purchase_intent_preference',
        'active',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'interests' => 'array',
        'active' => 'boolean',
    ];

    /**
     * @return BelongsToMany<ProductOpportunity, $this>
     */
    public function opportunities(): BelongsToMany
    {
        return $this->belongsToMany(
            ProductOpportunity::class,
            'product_opportunity_audience_segment',
            'audience_segment_id',
            'product_opportunity_id'
        );
    }

    /**
     * Scope para segmentos ativos
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope para segmentos inativos
     */
    public function scopeInactive($query)
    {
        return $query->where('active', false);
    }
}
