<?php

namespace App\Console\Commands;

use App\Models\Batch;
use App\Models\Bom;
use App\Models\CentralKitchen;
use App\Models\Costing;
use App\Models\Delivery;
use App\Models\GoodsReceipt;
use App\Models\Ingredient;
use App\Models\InventoryMovement;
use App\Models\Menu;
use App\Models\Organization;
use App\Models\Product;
use App\Models\ProductionOrder;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\Recall;
use App\Models\Recipe;
use App\Models\School;
use App\Models\Supplier;
use App\Models\SupplierInvoice;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\Waste;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class TenantExportCommand extends Command
{
    protected $signature = 'mbg:tenant-export {organization : ID atau kode organisasi}';

    protected $description = 'Export data satu organisasi ke JSON (backup/portabilitas tenant).';

    public function handle(): int
    {
        $key = $this->argument('organization');
        $org = Organization::where('id', $key)->orWhere('code', $key)->first();
        if (! $org) {
            $this->error('Organisasi tidak ditemukan.');

            return self::FAILURE;
        }
        $oid = $org->id;
        $data = ['organization' => $org->toArray(), 'exported_at' => now()->toDateTimeString()];
        $tables = [
            'central_kitchens' => CentralKitchen::class,
            'warehouses' => Warehouse::class,
            'suppliers' => Supplier::class,
            'schools' => School::class,
            'ingredients' => Ingredient::class,
            'products' => Product::class,
            'menus' => Menu::class,
            'recipes' => Recipe::class,
            'boms' => Bom::class,
            'users' => User::class,
            'batches' => Batch::class,
            'inventory_movements' => InventoryMovement::class,
            'purchase_requests' => PurchaseRequest::class,
            'purchase_orders' => PurchaseOrder::class,
            'goods_receipts' => GoodsReceipt::class,
            'supplier_invoices' => SupplierInvoice::class,
            'production_orders' => ProductionOrder::class,
            'deliveries' => Delivery::class,
            'wastes' => Waste::class,
            'costings' => Costing::class,
            'recalls' => Recall::class,
        ];
        foreach ($tables as $key => $class) {
            $data[$key] = $class::where('organization_id', $oid)->get()->toArray();
            $this->info(sprintf('%-22s %d baris', $key, count($data[$key])));
        }
        $file = "tenant-exports/org-{$oid}-".now()->format('Ymd-His').'.json';
        Storage::disk('local')->put($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $this->info('Tersimpan: storage/app/'.$file);

        return self::SUCCESS;
    }
}
