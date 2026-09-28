<?php

namespace App\Providers;

use App\Events\DeliveryCompleted;
use App\Events\GoodsReceived;
use App\Events\InspectionFailed;
use App\Events\ProductionCompleted;
use App\Events\QcFailed;
use App\Events\RecallCreated;
use App\Listeners\DispatchEventWebhooks;
use App\Listeners\RecordAuthAudit;
use App\Models\Batch;
use App\Models\Bom;
use App\Models\Delivery;
use App\Models\GoodsReceipt;
use App\Models\ProductionOrder;
use App\Models\PurchaseOrder;
use App\Models\Recall;
use App\Models\SupplierInvoice;
use App\Policies\BatchPolicy;
use App\Policies\BomPolicy;
use App\Policies\DeliveryPolicy;
use App\Policies\GoodsReceiptPolicy;
use App\Policies\ProductionOrderPolicy;
use App\Policies\PurchaseOrderPolicy;
use App\Policies\RecallPolicy;
use App\Policies\SupplierInvoicePolicy;
use App\Services\AiAdvisorInterface;
use App\Services\DeterministicAdvisor;
use App\View\Composers\SidebarComposer;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        View::composer(['layouts.*', 'admin.*', 'dashboard'], SidebarComposer::class);

        Gate::before(function ($user, $ability) {
            if ($user && method_exists($user, 'hasRole') && $user->hasRole(['super-admin', 'admin'])) {
                return true;
            }

            return null;
        });

        RateLimiter::for('api', fn (Request $r) => Limit::perMinute(120)->by($r->user()?->id ?: $r->ip()));
        RateLimiter::for('login', fn (Request $r) => Limit::perMinute(10)->by(strtolower((string) $r->input('email')).'|'.$r->ip()));
        RateLimiter::for('2fa', fn (Request $r) => Limit::perMinute(5)->by($r->session()->get('2fa:user_id', $r->ip())));

        foreach ([GoodsReceived::class, ProductionCompleted::class, QcFailed::class, InspectionFailed::class, DeliveryCompleted::class, RecallCreated::class] as $event) {
            Event::listen($event, DispatchEventWebhooks::class);
        }
        Event::listen([Login::class, Logout::class], RecordAuthAudit::class);

        foreach ([
            PurchaseOrder::class => PurchaseOrderPolicy::class,
            GoodsReceipt::class => GoodsReceiptPolicy::class,
            ProductionOrder::class => ProductionOrderPolicy::class,
            Delivery::class => DeliveryPolicy::class,
            Batch::class => BatchPolicy::class,
            Recall::class => RecallPolicy::class,
            SupplierInvoice::class => SupplierInvoicePolicy::class,
            Bom::class => BomPolicy::class,
        ] as $model => $policy) {
            Gate::policy($model, $policy);
        }

        $this->app->singleton(AiAdvisorInterface::class, DeterministicAdvisor::class);

        Paginator::useBootstrapFive();
    }
}
