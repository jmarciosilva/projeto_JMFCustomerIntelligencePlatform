<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IntegrationLog extends Model
{
    public const STATUS_SUCCESS = 'success';

    public const STATUS_PARTIAL = 'partial';

    public const STATUS_FAILED = 'failed';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'application_id',
        'integration',
        'status',
        'message',
        'items_processed',
        'items_failed',
        'duration_ms',
        'context',
        'occurred_at',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'items_processed' => 'integer',
        'items_failed' => 'integer',
        'duration_ms' => 'integer',
        'context' => 'array',
        'occurred_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<Application, $this>
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }
}
