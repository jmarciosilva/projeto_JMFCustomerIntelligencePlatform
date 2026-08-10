<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AffiliateConversion extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id',
        'affiliate_product_id',
        'affiliate_program_id',
        'campaign_id',
        'affiliate_link_id',
        'order_reference',
        'order_date',
        'product_price',
        'commission_rate',
        'commission_value',
        'status',
        'notes',
    ];

    protected $casts = [
        'order_date' => 'datetime',
        'product_price' => 'float',
        'commission_rate' => 'float',
        'commission_value' => 'float',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_PAID = 'paid';
    const STATUS_CANCELLED = 'cancelled';

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function affiliateProduct(): BelongsTo
    {
        return $this->belongsTo(AffiliateProduct::class);
    }

    public function affiliateProgram(): BelongsTo
    {
        return $this->belongsTo(AffiliateProgram::class);
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class)->nullable();
    }

    public function affiliateLink(): BelongsTo
    {
        return $this->belongsTo(AffiliateLink::class)->nullable();
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function approve(): void
    {
        $this->update(['status' => self::STATUS_APPROVED]);
    }

    public function markAsPaid(): void
    {
        $this->update(['status' => self::STATUS_PAID]);
    }

    public function cancel(): void
    {
        $this->update(['status' => self::STATUS_CANCELLED]);
    }
}
