<?php

namespace App\Core\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SettingService
{
    protected $cacheKey = 'mbg_settings';

    protected $cacheTTL = 3600;

    public function get(string $key, $default = null)
    {
        $settings = $this->getAll();

        return $settings[$key] ?? $default;
    }

    public function getAll(): array
    {
        return Cache::remember($this->cacheKey, $this->cacheTTL, function () {
            $data = DB::table('settings')->pluck('value', 'key')->toArray();

            return $data;
        });
    }

    public function set(string $key, $value): void
    {
        DB::transaction(function () use ($key, $value) {
            DB::table('settings')->updateOrInsert(
                ['key' => $key],
                ['value' => json_encode($value), 'updated_at' => now()]
            );
        });
        Cache::forget($this->cacheKey);
    }

    public function setMany(array $data): void
    {
        DB::transaction(function () use ($data) {
            foreach ($data as $key => $value) {
                DB::table('settings')->updateOrInsert(
                    ['key' => $key],
                    ['value' => json_encode($value), 'updated_at' => now()]
                );
            }
        });
        Cache::forget($this->cacheKey);
    }

    public function has(string $key): bool
    {
        return DB::table('settings')->where('key', $key)->exists();
    }

    public function remove(string $key): void
    {
        DB::table('settings')->where('key', $key)->delete();
        Cache::forget($this->cacheKey);
    }
}
