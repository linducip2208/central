<?php

namespace App\Console\Commands;

use App\Core\Services\NotificationService;
use App\Models\Costing;
use App\Models\Delivery;
use App\Models\InventoryMovement;
use App\Models\ProductionOrder;
use App\Models\ScheduledReport;
use App\Models\User;
use App\Models\Waste;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class RunScheduledReportsCommand extends Command
{
    protected $signature = 'mbg:run-scheduled-reports';

    protected $description = 'Generate laporan terjadwal (CSV ke storage + notifikasi admin).';

    public function handle(NotificationService $notifications): int
    {
        $count = 0;
        foreach (ScheduledReport::where('is_active', true)->get() as $schedule) {
            if (! $schedule->isDue()) {
                continue;
            }
            $filename = "scheduled/{$schedule->organization_id}/mbg-{$schedule->dataset}-".now()->format('Ymd-His').'.csv';
            $columns = match ($schedule->dataset) {
                'deliveries' => ['id', 'number', 'delivery_date', 'school_id', 'qty_planned', 'qty_delivered', 'status'],
                'production' => ['id', 'number', 'production_date', 'product_id', 'planned_qty', 'produced_qty', 'status'],
                'waste' => ['id', 'number', 'waste_date', 'item_type', 'item_id', 'qty', 'reason', 'cost_loss'],
                'costing' => ['id', 'costing_date', 'production_order_id', 'total_cost', 'portions', 'cost_per_portion'],
                default => ['id', 'movement_date', 'warehouse_id', 'item_type', 'item_id', 'movement_type', 'direction', 'qty', 'total_cost', 'reference_no'],
            };
            $query = match ($schedule->dataset) {
                'deliveries' => Delivery::where('organization_id', $schedule->organization_id)->latest()->take(1000),
                'production' => ProductionOrder::where('organization_id', $schedule->organization_id)->latest()->take(1000),
                'waste' => Waste::where('organization_id', $schedule->organization_id)->latest()->take(1000),
                'costing' => Costing::where('organization_id', $schedule->organization_id)->latest()->take(1000),
                default => InventoryMovement::where('organization_id', $schedule->organization_id)->latest()->take(1000),
            };
            $lines = [implode(',', $columns)];
            foreach ($query->cursor() as $row) {
                $lines[] = implode(',', array_map(fn ($c) => '"'.str_replace('"', '""', (string) ($row->{$c} ?? '')).'"', $columns));
            }
            Storage::disk('local')->put($filename, implode("\n", $lines));
            $schedule->update(['last_run_at' => now()]);
            $notifications->send(
                User::where('organization_id', $schedule->organization_id)->whereHas('roles', fn ($q) => $q->whereIn('name', ['super-admin', 'admin']))->get(),
                'scheduled_report',
                ['dataset' => $schedule->dataset, 'file' => $filename]
            );
            $count++;
            $this->info("OK: {$schedule->dataset} → {$filename}");
        }
        $this->info("Selesai: {$count} laporan.");

        return self::SUCCESS;
    }
}
