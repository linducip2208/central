<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class FeatureFlag extends Model
{
    protected $fillable = ['key', 'is_enabled', 'description'];

    protected function casts(): array
    {
        return ['is_enabled' => 'boolean'];
    }

    public static function enabled(string $key, bool $default = false): bool
    {
        return (bool) Cache::remember("ff_{$key}", 300, function () use ($key, $default) {
            return static::where('key', $key)->value('is_enabled') ?? $default;
        });
    }

    public static function flush(string $key): void
    {
        Cache::forget("ff_{$key}");
    }
}
