<?php

namespace App\Core\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class HealthService
{
    public function check(): array
    {
        return [
            'app' => $this->checkApp(),
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'queue' => $this->checkQueue(),
            'storage' => $this->checkStorage(),
        ];
    }

    public function checkApp(): array
    {
        return [
            'status' => 'healthy',
            'app_name' => config('app.name'),
            'app_env' => config('app.env'),
            'app_debug' => config('app.debug'),
            'app_version' => '1.0.0',
            'php_version' => PHP_VERSION,
        ];
    }

    public function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();
            $migrations = DB::table('migrations')->count();

            return ['status' => 'healthy', 'connected' => true, 'migrations' => $migrations];
        } catch (\Exception $e) {
            return ['status' => 'unhealthy', 'connected' => false, 'error' => $e->getMessage()];
        }
    }

    public function checkCache(): array
    {
        try {
            Cache::put('health_check', 'ok', now()->addSeconds(1));

            return ['status' => 'healthy', 'driver' => config('cache.default')];
        } catch (\Exception $e) {
            return ['status' => 'unhealthy', 'error' => $e->getMessage()];
        }
    }

    public function checkQueue(): array
    {
        try {
            $failedJobs = DB::table('failed_jobs')->count();

            return ['status' => 'healthy', 'driver' => config('queue.default'), 'failed_jobs' => $failedJobs];
        } catch (\Exception $e) {
            return ['status' => 'unhealthy', 'error' => $e->getMessage()];
        }
    }

    public function checkStorage(): array
    {
        try {
            $storagePath = storage_path();
            $disk = disk_free_space($storagePath);
            $diskTotal = disk_total_space($storagePath);

            return [
                'status' => 'healthy',
                'free_space' => $disk,
                'total_space' => $diskTotal,
                'usage_percent' => round(($diskTotal - $disk) / $diskTotal * 100, 2),
            ];
        } catch (\Exception $e) {
            return ['status' => 'unhealthy', 'error' => $e->getMessage()];
        }
    }
}
