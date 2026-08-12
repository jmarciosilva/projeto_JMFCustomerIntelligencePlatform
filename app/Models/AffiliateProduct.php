<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AffiliateProduct extends Model
{
    use HasFactory;

    public const AVAILABILITY_IN_STOCK = 'in_stock';

    public const AVAILABILITY_OUT_OF_STOCK = 'out_of_stock';

    public const AVAILABILITY_UNKNOWN = 'unknown';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'application_id',
        'affiliate_program_id',
        'external_product_id',
        'name',
        'description',
        'category',
        'brand',
        'price',
        'original_price',
        'commission_percentage',
        'estimated_commission',
        'affiliate_url',
        'image_url',
        'availability',
        'last_checked_at',
        'price_checked_at',
        'availability_checked_at',
        'metadata',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
        'original_price' => 'decimal:2',
        'commission_percentage' => 'decimal:2',
        'estimated_commission' => 'decimal:2',
        'last_checked_at' => 'datetime',
        'price_checked_at' => 'datetime',
        'availability_checked_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * @return BelongsTo<Application, $this>
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    /**
     * @return BelongsTo<AffiliateProgram, $this>
     */
    public function affiliateProgram(): BelongsTo
    {
        return $this->belongsTo(AffiliateProgram::class);
    }

    /**
     * @return HasMany<ProductOpportunity, $this>
     */
    public function opportunities(): HasMany
    {
        return $this->hasMany(ProductOpportunity::class, 'affiliate_product_id');
    }

    public function getProductNameAttribute(): string
    {
        return $this->name;
    }

    /**
     * Scope para produtos com dados desatualizados
     */
    public function scopeWithStaleData($query, int $days = 7)
    {
        $threshold = now()->subDays($days);

        return $query->where(function ($q) use ($threshold) {
            $q->whereNull('price_checked_at')
                ->orWhere('price_checked_at', '<', $threshold)
                ->orWhereNull('availability_checked_at')
                ->orWhere('availability_checked_at', '<', $threshold);
        });
    }

    /**
     * Scope para produtos com dados atualizados
     */
    public function scopeWithFreshData($query, int $days = 7)
    {
        $threshold = now()->subDays($days);

        return $query->whereNotNull('price_checked_at')
            ->where('price_checked_at', '>=', $threshold)
            ->whereNotNull('availability_checked_at')
            ->where('availability_checked_at', '>=', $threshold);
    }
}
