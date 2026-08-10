<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductOpportunity extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_ARCHIVED = 'archived';

    public const INTENT_HIGH = 'high';

    public const INTENT_MEDIUM = 'medium';

    public const INTENT_LOW = 'low';

    protected $table = 'product_opportunities';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'trend_id',
        'affiliate_product_id',
        'opportunity_score',
        'opportunity_breakdown',
        'commercial_intent',
        'status',
        'calculated_at',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'opportunity_score' => 'decimal:2',
        'opportunity_breakdown' => 'array',
        'calculated_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<Trend, $this>
     */
    public function trend(): BelongsTo
    {
        return $this->belongsTo(Trend::class);
    }

    /**
     * @return BelongsTo<AffiliateProduct, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(AffiliateProduct::class, 'affiliate_product_id');
    }

    /**
     * Retorna score 0-100 normalizado
     */
    public function getScore(): float
    {
        return (float) $this->opportunity_score;
    }

    /**
     * Determina cor da badge baseada no score
     */
    public function getScoreColor(): string
    {
        $score = $this->getScore();

        if ($score >= 75) {
            return 'green';
        }

        if ($score >= 50) {
            return 'yellow';
        }

        return 'red';
    }
}
