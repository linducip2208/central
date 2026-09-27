<?php

namespace App\Console\Commands;

use App\Core\Services\NotificationService;
use App\Models\Ingredient;
use App\Models\InventoryStock;
use Illuminate\Console\Command;

class LowStockCheckCommand extends Command
{
    protected $signature = 'mbg:low-stock-check';

    protected $description = 'Notifikasi bahan di bawah stok minimum (agregat semua gudang).';

    public function handle(NotificationService $notifications): int
    {
        $low = Ingredient::active()->with('unit')->get()
            ->map(function ($ing) {
                $stock = (float) InventoryStock::where('item_type', 'ingredient')->where('item_id', $ing->id)->sum('qty');

                return ['name' => $ing->name, 'code' => $ing->code, 'stock' => $stock, 'min' => (float) $ing->min_stock, 'unit' => $ing->unit->symbol ?? ''];
            })
            ->filter(fn ($r) => $r['stock'] <= $r['min'])
            ->values()
            ->toArray();

        if ($low) {
            $notifications->sendLowStockAlert($low);
        }

        $this->info('Bahan di bawah minimum: '.count($low).' item.');

        return self::SUCCESS;
    }
}
