<?php

namespace App\Models;

use App\Enums\MatchStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrendProductMatch extends Model
{
    use HasFactory;

    protected $table = 'trend_product_matches';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'trend_id',
        'affiliate_product_id',
        'match_score',
        'match_breakdown',
        'match_status',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'match_score' => 'decimal:2',
        'match_breakdown' => 'array',
        'match_status' => MatchStatus::class,
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
}
