<?php

namespace App\Providers;

use App\Core\Services\AuditService;
use App\Core\Services\HealthService;
use App\Core\Services\NotificationService;
use App\Core\Services\SettingService;
use App\Services\CostingService;
use App\Services\InventoryService;
use App\Services\NumberService;
use App\Services\WhatsApp\LogWhatsAppProvider;
use App\Services\WhatsApp\WhatsAppProvider;
use Illuminate\Support\ServiceProvider;

class MbgCoreProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SettingService::class);
        $this->app->singleton('settings', fn ($app) => $app->make(SettingService::class));
        $this->app->singleton(AuditService::class);
        $this->app->singleton(HealthService::class);
        $this->app->singleton(NotificationService::class);
        $this->app->singleton(InventoryService::class);
        $this->app->singleton(CostingService::class);
        $this->app->singleton(NumberService::class);
        $this->app->singleton(WhatsAppProvider::class, function () {
            return match (config('services.whatsapp.provider', 'log')) {
                // Provider API agregator dipasang di sini bila dikonfigurasi;
                // default aman: adapter log (tanpa kredensial).
                default => new LogWhatsAppProvider,
            };
        });
    }

    public function boot(): void
    {
        require_once app_path('Core/Support/helpers.php');
    }
}
