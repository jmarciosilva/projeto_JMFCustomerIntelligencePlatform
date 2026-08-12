<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrendingTopic extends Model
{
    protected $fillable = [
        'topic',
        'description',
        'search_volume',
        'growth_percentage',
        'category',
        'region',
        'fetched_at',
    ];

    protected $casts = [
        'fetched_at' => 'datetime',
    ];

    public static function getLatest(string $region = 'BR', ?string $category = null)
    {
        $query = static::where('region', $region)->orderByDesc('fetched_at');
        if ($category) {
            $query->where('category', $category);
        }

        return $query->get();
    }
}
