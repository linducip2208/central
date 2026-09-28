<?php

namespace App\Console\Commands;

use App\Core\Services\NotificationService;
use App\Models\Batch;
use App\Services\AutomationService;
use Illuminate\Console\Command;

class ExpiryCheckCommand extends Command
{
    protected $signature = 'mbg:expiry-check {--days= : Override batas hari}';

    protected $description = 'Tandai batch kedaluwarsa + notifikasi batch mendekati expired (FEFO guard).';

    public function handle(NotificationService $notifications): int
    {
        $days = (int) ($this->option('days') ?: config('mbg.inventory.expiry_alert_days', 30));

        $expired = Batch::expired()->get();
        foreach ($expired as $batch) {
            if ($batch->status !== 'EXPIRED') {
                $batch->update(['status' => 'EXPIRED']);
            }
        }

        $soon = Batch::available()
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<=', now()->addDays($days))
            ->with(['warehouse'])
            ->get()
            ->map(fn ($b) => ['batch_no' => $b->batch_no, 'expiry' => $b->expiry_date?->toDateString(), 'qty' => $b->remaining_qty, 'warehouse' => $b->warehouse->name ?? '-'])
            ->toArray();

        if ($soon) {
            $notifications->sendExpiryAlert($soon);
            app(AutomationService::class)->fire('stock.expiry', ['count' => count($soon), 'message' => count($soon)." batch expired/≤{$days} hari."]);
        }

        $this->info("Expired ditandai: {$expired->count()} batch. Mendekati expired (≤{$days} hari): ".count($soon).' batch.');

        return self::SUCCESS;
    }
}
