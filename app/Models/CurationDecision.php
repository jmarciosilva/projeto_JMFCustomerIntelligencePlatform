<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CurationDecision extends Model
{
    use HasFactory;

    protected $table = 'curation_decisions';

    protected $fillable = [
        'application_id',
        'product_opportunity_id',
        'user_id',
        'decision',
        'reason',
        'decided_at',
    ];

    protected $casts = [
        'decided_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    const DECISION_APPROVED = 'APPROVED';

    const DECISION_REJECTED = 'REJECTED';

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(ProductOpportunity::class, 'product_opportunity_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isApproved(): bool
    {
        return $this->decision === self::DECISION_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->decision === self::DECISION_REJECTED;
    }
}
