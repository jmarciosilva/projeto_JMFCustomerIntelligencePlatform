<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformSetting extends Model
{
    protected $fillable = [
        'application_id',
        'key',
        'value',
        'category',
        'is_secret',
        'description',
    ];

    public static function get(string $key, $default = null)
    {
        $setting = static::where('key', $key)
            ->where('application_id', auth()->user()?->application_id)
            ->first();

        return $setting?->value ?? $default;
    }

    public static function set(string $key, $value, string $category = 'general', string $description = ''): void
    {
        static::updateOrCreate(
            ['key' => $key, 'application_id' => auth()->user()?->application_id],
            [
                'value' => $value,
                'category' => $category,
                'description' => $description,
                'is_secret' => in_array($category, ['api_keys', 'credentials']),
            ]
        );
    }

    public static function getByCategory(string $category)
    {
        return static::where('category', $category)
            ->where('application_id', auth()->user()?->application_id)
            ->get();
    }
}
