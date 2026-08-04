<?php

namespace App\Support\Slug;

use Illuminate\Support\Str;

class UniqueSlugGenerator
{
    /**
     * @param  callable(string): bool  $exists
     */
    public static function generate(string $name, callable $exists): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $suffix = 2;

        while ($exists($slug)) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
