<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contact extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'tenant_id',
        'external_id',
        'email',
        'phone',
        'name',
        'properties',
        'first_identified_at',
        'last_seen_at',
        'lead_score',
        'lead_score_computed_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'properties' => 'array',
            'first_identified_at' => 'datetime',
            'last_seen_at' => 'datetime',
            'lead_score' => 'integer',
            'lead_score_computed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Tenant, $this>
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * @return HasMany<Visitor, $this>
     */
    public function visitors(): HasMany
    {
        return $this->hasMany(Visitor::class);
    }

    /**
     * @return HasMany<ContactConsent, $this>
     */
    public function consents(): HasMany
    {
        return $this->hasMany(ContactConsent::class);
    }

    /**
     * @param  Builder<Contact>  $query
     * @return Builder<Contact>
     */
    public function scopeInactive(Builder $query, int $days = 30): Builder
    {
        return $query->where('last_seen_at', '<', now()->subDays($days));
    }
}
