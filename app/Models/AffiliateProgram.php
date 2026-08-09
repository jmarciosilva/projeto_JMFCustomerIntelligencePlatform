<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AffiliateProgram extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    public const PROVIDER_MANUAL = 'manual';

    public const PROVIDER_MAGALU = 'magalu';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'application_id',
        'name',
        'slug',
        'website',
        'description',
        'provider',
        'status',
        'configuration',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'configuration' => 'array',
    ];

    /**
     * @return BelongsTo<Application, $this>
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    /**
     * @return HasMany<AffiliateProduct, $this>
     */
    public function products(): HasMany
    {
        return $this->hasMany(AffiliateProduct::class);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }
}
