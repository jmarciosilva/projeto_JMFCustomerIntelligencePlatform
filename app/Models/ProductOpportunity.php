<?php

namespace App\Models;

use App\Domain\Affiliate\Enums\PurchaseIntentLabel;
use App\Domain\Affiliate\Enums\StatusSprintA;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductOpportunity extends Model
{
    use HasFactory;

    protected $table = 'product_opportunities';

    protected $fillable = [
        'application_id',
        'status_sprint_a',
        'discovery_opportunity_score',
        'opportunity_score_breakdown',
        'purchase_intent_score',
        'purchase_intent_label',
        'purchase_intent_breakdown',
        'actual_performance_score',
        'performance_score_breakdown',
        'recurrency_rate',
        'confidence_level',
        'approved_at',
        'published_at',
        'expired_at',
        'expires_at',
        'expiration_reason',
        'trend_id',
        'affiliate_product_id',
        'opportunity_score',
        'opportunity_breakdown',
        'commercial_intent',
        'status',
        'calculated_at',
    ];

    protected $casts = [
        'status_sprint_a' => StatusSprintA::class,
        'purchase_intent_label' => PurchaseIntentLabel::class,
        'opportunity_score_breakdown' => 'json',
        'purchase_intent_breakdown' => 'json',
        'performance_score_breakdown' => 'json',
        'opportunity_breakdown' => 'json',
        'discovery_opportunity_score' => 'integer',
        'purchase_intent_score' => 'integer',
        'actual_performance_score' => 'integer',
        'recurrency_rate' => 'decimal:2',
        'opportunity_score' => 'decimal:2',
        'approved_at' => 'datetime',
        'published_at' => 'datetime',
        'expired_at' => 'datetime',
        'expires_at' => 'datetime',
        'calculated_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class, 'application_id');
    }

    public function trend(): BelongsTo
    {
        return $this->belongsTo(Trend::class, 'trend_id');
    }

    public function affiliateProduct(): BelongsTo
    {
        return $this->belongsTo(AffiliateProduct::class, 'affiliate_product_id');
    }

    public function affiliateLinks(): HasMany
    {
        return $this->hasMany(AffiliateLink::class, 'product_opportunity_id');
    }

    public function affiliateConversions(): HasMany
    {
        return $this->hasMany(AffiliateConversion::class, 'product_opportunity_id');
    }

    public function curationDecisions(): HasMany
    {
        return $this->hasMany(CurationDecision::class, 'product_opportunity_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeByApplication($query, $applicationId)
    {
        return $query->where('application_id', $applicationId);
    }

    public function scopeByStatus($query, StatusSprintA | string $status)
    {
        $value = $status instanceof StatusSprintA ? $status->value : $status;
        return $query->where('status_sprint_a', $value);
    }

    public function scopeDiscovered($query)
    {
        return $query->where('status_sprint_a', StatusSprintA::DISCOVERED->value);
    }

    public function scopeAnalyzing($query)
    {
        return $query->where('status_sprint_a', StatusSprintA::ANALYZING->value);
    }

    public function scopeApproved($query)
    {
        return $query->where('status_sprint_a', StatusSprintA::APPROVED->value);
    }

    public function scopeExpiringSoon($query, $days = 7)
    {
        return $query
            ->whereIn('status_sprint_a', [StatusSprintA::DISCOVERED->value, StatusSprintA::ANALYZING->value])
            ->where('expires_at', '<=', now()->addDays($days))
            ->where('expires_at', '>', now());
    }

    public function scopeExpired($query)
    {
        return $query->where('status_sprint_a', StatusSprintA::EXPIRED->value);
    }

    public function scopeRecentFirst($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    public function scopeByOpportunitScore($query, $order = 'desc')
    {
        return $query->orderBy('opportunity_score', $order);
    }

    public function scopeByConfidenceLevel($query, string $level)
    {
        return $query->where('confidence_level', $level);
    }

    public function scopeHighConfidence($query)
    {
        return $query->where('confidence_level', 'HIGH');
    }

    public function scopeWithRecurrency($query)
    {
        return $query->whereNotNull('recurrency_rate');
    }
}
