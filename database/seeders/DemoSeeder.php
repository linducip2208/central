<?php

namespace Database\Seeders;

use App\Models\CentralKitchen;
use App\Models\Delivery;
use App\Models\Demand;
use App\Models\Distribution;
use App\Models\GoodsReceipt;
use App\Models\Ingredient;
use App\Models\Menu;
use App\Models\Organization;
use App\Models\Packaging;
use App\Models\Product;
use App\Models\ProductionOrder;
use App\Models\ProductionPlan;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\QualityControl;
use App\Models\School;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\Waste;
use App\Services\CostingService;
use App\Services\InventoryService;
use App\Services\NumberService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Demo operasional 1 hari yang KONSISTEN lewat ledger:
 * demand → PR → PO → GR(receive) → produksi(consume+output) → QC → packaging → distribusi → delivery.
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $numbers = app(NumberService::class);
        $inventory = app(InventoryService::class);

        $org = Organization::where('code', 'MBG-01')->first() ?? Organization::first();
        $ck = CentralKitchen::where('organization_id', $org?->id)->first() ?? CentralKitchen::first();
        $wh = Warehouse::whereHas('centralKitchen', fn ($q) => $q->where('organization_id', $org?->id))->first()
            ?? Warehouse::where('code', 'WH-DRY-01')->first();
        $admin = User::where('email', 'admin@mbg.id')->first();
        if (! $org || ! $ck || ! $wh) {
            $this->command->warn('MasterSeeder belum dijalankan. Lewati demo.');

            return;
        }

        if (PurchaseRequest::exists()) {
            $this->command->info('Demo sudah ada, lewati.');

            return;
        }

        DB::transaction(function () use ($numbers, $inventory, $org, $ck, $wh, $admin) {
            $menu = Menu::where('status', 'APPROVED')->first();
            $portions = 1000;

            // 1. Demand
            $demand = Demand::create([
                'organization_id' => $org->id, 'central_kitchen_id' => $ck->id,
                'menu_id' => $menu?->id, 'code' => $numbers->next('DM'),
                'demand_date' => now()->toDateString(), 'portions' => $portions,
                'qty' => $portions, 'source' => 'SCHOOL', 'status' => 'PLANNED', 'created_by' => $admin?->id,
            ]);

            // 2. PR dari kebutuhan resep menu
            $needs = [];
            if ($menu) {
                $menu->load('items.product.activeRecipe.items');
                foreach ($menu->items as $mi) {
                    $recipe = $mi->product->activeRecipe;
                    if (! $recipe) {
                        continue;
                    }
                    foreach ($recipe->items as $ri) {
                        $needs[$ri->ingredient_id] = ($needs[$ri->ingredient_id] ?? 0) + $ri->requiredFor($portions * (float) $mi->qty_per_portion, (float) $recipe->yield_qty);
                    }
                }
            }
            if (empty($needs)) {
                $beras = Ingredient::where('code', 'ING-BERAS')->first();
                $needs[$beras->id] = 120;
            }

            $pr = PurchaseRequest::create([
                'organization_id' => $org->id, 'central_kitchen_id' => $ck->id, 'warehouse_id' => $wh->id,
                'number' => $numbers->next('PR'), 'request_date' => now()->subDays(2)->toDateString(),
                'needed_date' => now()->toDateString(), 'status' => 'APPROVED',
                'requested_by' => $admin?->id, 'approved_by' => $admin?->id, 'approved_at' => now()->subDay(),
            ]);
            foreach ($needs as $ingId => $qty) {
                $ing = Ingredient::find($ingId);
                $pr->items()->create(['ingredient_id' => $ingId, 'qty_requested' => round($qty, 3), 'qty_approved' => round($qty, 3), 'unit_id' => $ing->unit_id, 'estimated_price' => $ing->standard_price]);
            }

            // 3. PO + 4. GR (terima penuh, posting stok)
            $suppliers = Supplier::active()->get();
            $poCount = 0;
            foreach ($pr->items as $prItem) {
                $supplier = $suppliers[$poCount % max(1, $suppliers->count())] ?? Supplier::first();
                $po = PurchaseOrder::create([
                    'organization_id' => $org->id, 'central_kitchen_id' => $ck->id,
                    'supplier_id' => $supplier->id, 'warehouse_id' => $wh->id,
                    'purchase_request_id' => $pr->id, 'number' => $numbers->next('PO'),
                    'order_date' => now()->subDay()->toDateString(), 'status' => 'APPROVED',
                    'payment_terms' => 'CREDIT', 'ordered_by' => $admin?->id,
                    'approved_by' => $admin?->id, 'approved_at' => now()->subDay(),
                ]);
                $poItem = $po->items()->create([
                    'purchase_request_item_id' => $prItem->id, 'ingredient_id' => $prItem->ingredient_id,
                    'qty_ordered' => $prItem->qty_approved, 'unit_id' => $prItem->unit_id,
                    'unit_price' => Ingredient::find($prItem->ingredient_id)->standard_price,
                ]);
                $prItem->update(['qty_ordered' => $prItem->qty_approved]);
                $po->recalculateTotals();

                $gr = GoodsReceipt::create([
                    'organization_id' => $org->id, 'central_kitchen_id' => $ck->id, 'warehouse_id' => $wh->id,
                    'purchase_order_id' => $po->id, 'supplier_id' => $supplier->id,
                    'number' => $numbers->next('GR'), 'receipt_date' => now()->toDateString(),
                    'status' => 'RECEIVED', 'received_by' => $admin?->id,
                ]);
                $gr->items()->create([
                    'purchase_order_item_id' => $poItem->id, 'ingredient_id' => $poItem->ingredient_id,
                    'qty_ordered' => $poItem->qty_ordered, 'qty_received' => $poItem->qty_ordered,
                    'unit_id' => $poItem->unit_id, 'unit_price' => $poItem->unit_price,
                    'batch_no' => 'DEMO-'.strtoupper(substr(md5((string) $poItem->id), 0, 6)),
                    'expiry_date' => now()->addDays(60)->toDateString(),
                ]);
                $inventory->receive([
                    'organization_id' => $org->id, 'warehouse_id' => $wh->id,
                    'item_type' => 'ingredient', 'item_id' => $poItem->ingredient_id,
                    'qty' => (float) $poItem->qty_ordered, 'unit_id' => $poItem->unit_id,
                    'unit_cost' => (float) $poItem->unit_price,
                    'batch_no' => 'DEMO-'.strtoupper(substr(md5((string) $poItem->id), 0, 6)),
                    'expiry_date' => now()->addDays(60)->toDateString(),
                    'supplier_id' => $supplier->id,
                    'reference_type' => GoodsReceipt::class, 'reference_id' => $gr->id, 'reference_no' => $gr->number,
                ]);
                $poItem->update(['qty_received' => $poItem->qty_ordered]);
                $po->refresh()->refreshReceiveStatus();
                $poCount++;
            }
            $pr->update(['status' => 'ORDERED']);

            // 5. Production plan + order + consume + output
            $product = Product::where('code', 'PRD-NASI-AYAM')->first();
            $plan = ProductionPlan::create([
                'organization_id' => $org->id, 'central_kitchen_id' => $ck->id, 'menu_id' => $menu?->id,
                'number' => $numbers->next('PP'), 'plan_date' => now()->toDateString(),
                'target_portions' => 500, 'status' => 'RELEASED', 'created_by' => $admin?->id,
                'approved_by' => $admin?->id, 'approved_at' => now(),
            ]);
            $plan->items()->create(['product_id' => $product->id, 'planned_qty' => 500]);

            $recipe = $product->activeRecipe;
            $order = ProductionOrder::create([
                'organization_id' => $org->id, 'central_kitchen_id' => $ck->id,
                'production_plan_id' => $plan->id, 'menu_id' => $menu?->id,
                'product_id' => $product->id, 'recipe_id' => $recipe?->id,
                'number' => $numbers->next('WO'), 'production_date' => now()->toDateString(),
                'planned_qty' => 500, 'unit_id' => $product->unit_id,
                'status' => 'IN_PROGRESS', 'started_at' => now()->subHours(3), 'created_by' => $admin?->id,
            ]);
            $materialCost = 0;
            if ($recipe) {
                foreach ($recipe->items as $ri) {
                    $need = $ri->requiredFor(500, (float) $recipe->yield_qty);
                    $order->items()->create(['ingredient_id' => $ri->ingredient_id, 'qty_required' => $need, 'qty_consumed' => $need, 'unit_id' => $ri->unit_id]);
                    try {
                        $allocs = $inventory->consume($wh->id, 'ingredient', $ri->ingredient_id, $need, [
                            'organization_id' => $org->id, 'movement_type' => 'PRODUCTION_CONSUMPTION',
                            'unit_id' => $ri->unit_id, 'reference_type' => ProductionOrder::class,
                            'reference_id' => $order->id, 'reference_no' => $order->number,
                        ]);
                        $materialCost += array_sum(array_map(fn ($a) => $a['qty'] * $a['unit_cost'], $allocs));
                    } catch (\Throwable) {
                        // stok demo mungkin kurang untuk sebagian item — lanjutkan
                    }
                }
            }
            $inventory->produceOutput([
                'organization_id' => $org->id, 'warehouse_id' => $wh->id, 'item_id' => $product->id,
                'qty' => 480, 'unit_id' => $product->unit_id, 'unit_cost' => 0,
                'batch_no' => 'DEMO-PRD-1', 'expiry_date' => now()->addDay()->toDateString(),
                'reference_type' => ProductionOrder::class, 'reference_id' => $order->id, 'reference_no' => $order->number,
            ]);
            $order->update(['produced_qty' => 480, 'rejected_qty' => 5, 'status' => 'COMPLETED', 'completed_at' => now()]);

            QualityControl::create([
                'organization_id' => $org->id, 'central_kitchen_id' => $ck->id,
                'reference_type' => ProductionOrder::class, 'reference_id' => $order->id,
                'number' => $numbers->next('QC'), 'check_date' => now()->toDateString(),
                'check_type' => 'ORGANOLEPTIC', 'sample_qty' => 10, 'pass_qty' => 10, 'fail_qty' => 0,
                'result' => 'PASSED', 'checked_by' => $admin?->id,
            ]);

            app(CostingService::class)->forProductionOrder($order->fresh(), ['labor_cost' => 750000, 'overhead_cost' => 500000, 'packaging_cost' => 480000]);

            $pkg = Packaging::create([
                'organization_id' => $org->id, 'central_kitchen_id' => $ck->id,
                'production_order_id' => $order->id, 'warehouse_id' => $wh->id,
                'number' => $numbers->next('PKG'), 'packaging_date' => now()->toDateString(),
                'packages_planned' => 480, 'packages_done' => 480, 'package_type' => 'BOX',
                'status' => 'COMPLETED', 'created_by' => $admin?->id,
            ]);
            $pkg->items()->create(['product_id' => $product->id, 'qty_packed' => 480]);

            // 6. Distribusi ke 3 sekolah
            $dist = Distribution::create([
                'organization_id' => $org->id, 'central_kitchen_id' => $ck->id, 'packaging_id' => $pkg->id,
                'number' => $numbers->next('DST'), 'distribution_date' => now()->toDateString(),
                'total_portions' => 480, 'vehicle_no' => 'B 1234 MBG', 'driver_name' => 'Kurir Demo',
                'status' => 'IN_TRANSIT', 'created_by' => $admin?->id,
            ]);
            $schools = School::active()->take(3)->get();
            $perSchool = (int) floor(480 / max(1, $schools->count()));
            foreach ($schools as $school) {
                $dist->items()->create(['school_id' => $school->id, 'product_id' => $product->id, 'qty_planned' => $perSchool, 'qty_delivered' => $perSchool]);
                $delivery = Delivery::create([
                    'organization_id' => $org->id, 'central_kitchen_id' => $ck->id,
                    'distribution_id' => $dist->id, 'school_id' => $school->id,
                    'number' => $numbers->next('DLV'), 'delivery_date' => now()->toDateString(),
                    'qty_planned' => $perSchool, 'qty_delivered' => $perSchool,
                    'status' => 'DELIVERED', 'dispatched_at' => now()->subHours(2),
                    'delivered_at' => now()->subHour(), 'received_by_name' => 'Petugas Sekolah',
                    'courier_id' => $admin?->id,
                ]);
                $delivery->items()->create(['product_id' => $product->id, 'qty_planned' => $perSchool, 'qty_delivered' => $perSchool]);
                $delivery->trackings()->create(['status' => 'PLANNED', 'notes' => 'Dibuat', 'created_by' => $admin?->id]);
                $delivery->trackings()->create(['status' => 'IN_TRANSIT', 'notes' => 'Berangkat', 'created_by' => $admin?->id]);
                $delivery->trackings()->create(['status' => 'DELIVERED', 'notes' => 'Diterima', 'created_by' => $admin?->id]);
            }
            $inventory->consume($wh->id, 'product', $product->id, 480, [
                'organization_id' => $org->id, 'movement_type' => 'DELIVERY',
                'unit_id' => $product->unit_id, 'reference_type' => Distribution::class,
                'reference_id' => $dist->id, 'reference_no' => $dist->number,
            ]);

            // 7. Waste contoh
            $bayam = Ingredient::where('code', 'ING-BAYAM')->first();
            if ($bayam && $inventory->stockOf($wh->id, 'ingredient', $bayam->id) >= 2) {
                $waste = Waste::create([
                    'organization_id' => $org->id, 'central_kitchen_id' => $ck->id, 'warehouse_id' => $wh->id,
                    'number' => $numbers->next('WST'), 'waste_date' => now()->toDateString(),
                    'item_type' => 'ingredient', 'item_id' => $bayam->id, 'qty' => 2,
                    'unit_id' => $bayam->unit_id, 'reason' => 'SPOILED', 'reported_by' => $admin?->id,
                ]);
                $allocs = $inventory->consume($wh->id, 'ingredient', $bayam->id, 2, [
                    'organization_id' => $org->id, 'movement_type' => 'WASTE',
                    'reference_type' => Waste::class, 'reference_id' => $waste->id, 'reference_no' => $waste->number,
                ]);
                $waste->update(['cost_loss' => array_sum(array_map(fn ($a) => $a['qty'] * $a['unit_cost'], $allocs))]);
            }
        });
    }
}
