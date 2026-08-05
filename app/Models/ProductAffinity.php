<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductAffinity extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'application_id',
        'subject_type',
        'subject_id_a',
        'subject_id_b',
        'co_occurrences',
        'computed_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'co_occurrences' => 'integer',
            'computed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Application, $this>
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }
}
