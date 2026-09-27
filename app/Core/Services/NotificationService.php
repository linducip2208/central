<?php

namespace App\Core\Services;

use App\Models\PurchaseOrder;
use App\Models\User;
use App\Notifications\MBGNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class NotificationService
{
    public function send(iterable $users, string $type, array $data): void
    {
        $users = $users instanceof Collection ? $users : collect($users);
        if ($users->isEmpty()) {
            return;
        }
        Notification::send($users, new MBGNotification($type, $data));

        foreach ($users as $user) {
            Cache::forget("user_notifications_{$user->id}");
        }
    }

    protected function roleUsers(array $roles, ?int $organizationId = null)
    {
        return User::active()
            ->when($organizationId, fn ($q) => $q->where('organization_id', $organizationId))
            ->whereHas('roles', fn ($q) => $q->whereIn('name', $roles))
            ->get();
    }

    public function sendLowStockAlert(array $items, ?int $organizationId = null): void
    {
        $this->send($this->roleUsers(['super-admin', 'admin', 'warehouse'], $organizationId), 'low_stock', ['items' => $items]);
    }

    public function sendExpiryAlert(array $items, ?int $organizationId = null): void
    {
        $this->send($this->roleUsers(['super-admin', 'admin', 'warehouse'], $organizationId), 'expiry_alert', ['items' => $items]);
    }

    public function sendProductionReady(int $productionOrderId, ?int $organizationId = null): void
    {
        $this->send($this->roleUsers(['super-admin', 'admin', 'kitchen'], $organizationId), 'production_ready', ['production_order_id' => $productionOrderId]);
    }

    public function sendPurchaseApproved(int $poId, ?int $organizationId = null): void
    {
        $po = PurchaseOrder::find($poId);
        $orgId = $organizationId ?? $po?->organization_id;
        $this->send(
            $this->roleUsers(['super-admin', 'admin', 'warehouse', 'procurement'], $orgId),
            'po_approved',
            ['purchase_order_id' => $poId, 'number' => $po?->number]
        );
    }

    public function getUnreadCount(int $userId): int
    {
        return (int) Cache::remember("user_notifications_{$userId}", 300, function () use ($userId) {
            return DB::table('notifications')
                ->where('notifiable_type', User::class)
                ->where('notifiable_id', $userId)
                ->whereNull('read_at')
                ->count();
        });
    }
}
