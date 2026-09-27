<?php

namespace Database\Seeders;

use App\Models\CentralKitchen;
use App\Models\Ingredient;
use App\Models\KitchenUnit;
use App\Models\Menu;
use App\Models\Organization;
use App\Models\Product;
use App\Models\Recipe;
use App\Models\School;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\UnitConversion;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class MasterSeeder extends Seeder
{
    public function run(): void
    {
        // ---- Units ----
        $kg = Unit::firstOrCreate(['code' => 'KG'], ['name' => 'Kilogram', 'symbol' => 'kg', 'unit_type' => 'WEIGHT', 'is_base' => true, 'is_active' => true]);
        $g = Unit::firstOrCreate(['code' => 'G'], ['name' => 'Gram', 'symbol' => 'g', 'unit_type' => 'WEIGHT', 'is_active' => true]);
        $l = Unit::firstOrCreate(['code' => 'L'], ['name' => 'Liter', 'symbol' => 'L', 'unit_type' => 'VOLUME', 'is_base' => true, 'is_active' => true]);
        $ml = Unit::firstOrCreate(['code' => 'ML'], ['name' => 'Mililiter', 'symbol' => 'mL', 'unit_type' => 'VOLUME', 'is_active' => true]);
        $pcs = Unit::firstOrCreate(['code' => 'PCS'], ['name' => 'Pieces', 'symbol' => 'pcs', 'unit_type' => 'COUNT', 'is_base' => true, 'is_active' => true]);
        $pack = Unit::firstOrCreate(['code' => 'PACK'], ['name' => 'Paket/Porsi', 'symbol' => 'pack', 'unit_type' => 'COUNT', 'is_active' => true]);
        $tray = Unit::firstOrCreate(['code' => 'TRAY'], ['name' => 'Nampan', 'symbol' => 'tray', 'unit_type' => 'COUNT', 'is_active' => true]);

        foreach ([[$kg->id, $g->id, 1000], [$l->id, $ml->id, 1000]] as [$from, $to, $factor]) {
            UnitConversion::firstOrCreate(['from_unit_id' => $from, 'to_unit_id' => $to], ['factor' => $factor]);
        }

        // ---- Organization ----
        $org = Organization::firstOrCreate(['code' => 'MBG-01'], [
            'name' => 'Yayasan MBG Sejahtera', 'slug' => 'yayasan-mbg-sejahtera',
            'email' => 'info@mbg.id', 'phone' => '021-5550100', 'address' => 'Jl. Gizi No. 1, Jakarta',
            'city' => 'Jakarta', 'province' => 'DKI Jakarta', 'status' => 'ACTIVE',
        ]);

        $ck = CentralKitchen::firstOrCreate(['code' => 'CK-JKT-01'], [
            'organization_id' => $org->id, 'name' => 'Central Kitchen Jakarta Timur', 'slug' => 'ck-jakarta-timur',
            'address' => 'Jl. Dapur No. 10', 'city' => 'Jakarta Timur',
            'pic_name' => 'Budi Santoso', 'pic_phone' => '0812000100', 'daily_capacity' => 5000, 'status' => 'ACTIVE',
        ]);

        KitchenUnit::firstOrCreate(['code' => 'KU-PROD-01'], ['central_kitchen_id' => $ck->id, 'name' => 'Unit Produksi A', 'unit_type' => 'PRODUCTION', 'capacity' => 3000, 'status' => 'ACTIVE']);
        KitchenUnit::firstOrCreate(['code' => 'KU-PACK-01'], ['central_kitchen_id' => $ck->id, 'name' => 'Unit Pengemasan', 'unit_type' => 'PACKAGING', 'capacity' => 5000, 'status' => 'ACTIVE']);

        $whDry = Warehouse::firstOrCreate(['code' => 'WH-DRY-01'], ['central_kitchen_id' => $ck->id, 'name' => 'Gudang Kering', 'warehouse_type' => 'DRY', 'pic_name' => 'Siti', 'is_default' => true, 'status' => 'ACTIVE']);
        Warehouse::firstOrCreate(['code' => 'WH-CHL-01'], ['central_kitchen_id' => $ck->id, 'name' => 'Gudang Dingin', 'warehouse_type' => 'CHILLED', 'pic_name' => 'Andi', 'status' => 'ACTIVE']);
        Warehouse::firstOrCreate(['code' => 'WH-FRZ-01'], ['central_kitchen_id' => $ck->id, 'name' => 'Gudang Beku', 'warehouse_type' => 'FROZEN', 'pic_name' => 'Dewi', 'status' => 'ACTIVE']);

        // ---- Suppliers ----
        $suppliers = [
            ['SUP-BERAS', 'PT Pangan Nusantara', 'FOOD', 'Haji Ahmad', '0813000101'],
            ['SUP-AYAM', 'CV Ternak Jaya', 'FOOD', 'Joko', '0813000102'],
            ['SUP-SAYUR', 'Koperasi Tani Segar', 'FOOD', 'Rina', '0813000103'],
            ['SUP-TELUR', 'PT Telur Emas', 'FOOD', 'Bambang', '0813000104'],
            ['SUP-KEMAS', 'PT Kemasindo', 'NON_FOOD', 'Lina', '0813000105'],
        ];
        foreach ($suppliers as [$code, $name, $cat, $cp, $phone]) {
            Supplier::firstOrCreate(['code' => $code], ['organization_id' => $org->id, 'name' => $name, 'category' => $cat, 'contact_person' => $cp, 'phone' => $phone, 'rating' => 4, 'status' => 'ACTIVE']);
        }

        // ---- Schools ----
        $levels = ['SD', 'SD', 'SMP', 'SD', 'SMA', 'TK', 'SMP', 'SD'];
        for ($i = 1; $i <= 8; $i++) {
            School::firstOrCreate(['code' => 'SCH-'.str_pad((string) $i, 3, '0', STR_PAD_LEFT)], [
                'organization_id' => $org->id, 'central_kitchen_id' => $ck->id,
                'npsn' => '2010'.str_pad((string) $i, 4, '0', STR_PAD_LEFT),
                'name' => ['SDN Cipinang 01', 'SDN Cipinang 02', 'SMPN 101 Jakarta', 'SDN Klender 05', 'SMAN 59 Jakarta', 'TK Pelita Bangsa', 'SMPN 88 Jakarta', 'SDN Duren Sawit 03'][$i - 1],
                'level' => $levels[$i - 1], 'district' => 'Cipayung', 'city' => 'Jakarta Timur',
                'pic_name' => 'Kepsek '.$i, 'pic_phone' => '0814000'.$i,
                'student_count' => [320, 280, 450, 310, 520, 120, 380, 295][$i - 1],
                'target_portions' => [320, 280, 450, 310, 520, 120, 380, 295][$i - 1],
                'distance_km' => [1.2, 2.5, 3.1, 4.0, 5.2, 1.8, 3.7, 2.2][$i - 1],
                'status' => 'ACTIVE',
            ]);
        }

        // ---- Ingredients ----
        $ingredients = [
            ['ING-BERAS', 'Beras Premium', 'STAPLE', $kg, 13500, 200, 2000, 180],
            ['ING-AYAM', 'Ayam Potong', 'PROTEIN', $kg, 38000, 50, 500, 2],
            ['ING-TELUR', 'Telur Ayam', 'PROTEIN', $kg, 28000, 30, 300, 14],
            ['ING-TAHU', 'Tahu Putih', 'PROTEIN', $pcs, 1500, 100, 2000, 2],
            ['ING-TEMPE', 'Tempe', 'PROTEIN', $pcs, 4000, 100, 1500, 3],
            ['ING-WORTEL', 'Wortel', 'VEGETABLE', $kg, 12000, 20, 200, 7],
            ['ING-BAYAM', 'Bayam', 'VEGETABLE', $kg, 8000, 15, 150, 2],
            ['ING-BUNCIS', 'Buncis', 'VEGETABLE', $kg, 14000, 15, 150, 4],
            ['ING-PISANG', 'Pisang Ambon', 'FRUIT', $pcs, 2500, 100, 2000, 5],
            ['ING-JERUK', 'Jeruk Medan', 'FRUIT', $kg, 18000, 20, 200, 7],
            ['ING-MINYAK', 'Minyak Goreng', 'OIL', $l, 20000, 20, 200, 365],
            ['ING-GARAM', 'Garam', 'SPICE', $kg, 12000, 10, 100, 730],
            ['ING-GULA', 'Gula Pasir', 'SPICE', $kg, 17500, 10, 150, 730],
            ['ING-BAWANG', 'Bawang Merah', 'SPICE', $kg, 32000, 10, 100, 14],
            ['ING-SUSU', 'Susu UHT 200ml', 'OTHER', $pcs, 4500, 200, 3000, 90],
        ];
        foreach ($ingredients as [$code, $name, $cat, $unit, $price, $min, $max, $shelf]) {
            Ingredient::firstOrCreate(['code' => $code], [
                'organization_id' => $org->id, 'name' => $name, 'category' => $cat,
                'unit_id' => $unit->id, 'standard_price' => $price,
                'min_stock' => $min, 'max_stock' => $max, 'shelf_life_days' => $shelf, 'is_active' => true,
            ]);
        }

        // ---- Products ----
        $products = [
            ['PRD-NASI-AYAM', 'Paket Nasi Ayam', 'MEAL', 450],
            ['PRD-NASI-TELUR', 'Paket Nasi Telur', 'MEAL', 420],
            ['PRD-NASI-TEMPE', 'Paket Nasi Tempe Tahu', 'MEAL', 400],
            ['PRD-SNACK', 'Paket Snack + Susu', 'SNACK', 250],
        ];
        foreach ($products as [$code, $name, $cat, $gram]) {
            Product::firstOrCreate(['code' => $code], [
                'organization_id' => $org->id, 'name' => $name, 'category' => $cat,
                'unit_id' => $pack->id, 'portion_size_gram' => $gram, 'is_active' => true,
            ]);
        }

        // ---- Recipes ----
        $byCode = fn (string $code) => Ingredient::where('code', $code)->firstOrFail();
        $recipes = [
            'PRD-NASI-AYAM' => [['ING-BERAS', 0.12, 'KG'], ['ING-AYAM', 0.08, 'KG'], ['ING-WORTEL', 0.05, 'KG'], ['ING-MINYAK', 0.015, 'L'], ['ING-GARAM', 0.003, 'KG'], ['ING-BAWANG', 0.01, 'KG']],
            'PRD-NASI-TELUR' => [['ING-BERAS', 0.12, 'KG'], ['ING-TELUR', 0.1, 'KG'], ['ING-BAYAM', 0.05, 'KG'], ['ING-MINYAK', 0.015, 'L'], ['ING-GARAM', 0.003, 'KG']],
            'PRD-NASI-TEMPE' => [['ING-BERAS', 0.12, 'KG'], ['ING-TEMPE', 1, 'PCS'], ['ING-TAHU', 1, 'PCS'], ['ING-BUNCIS', 0.05, 'KG'], ['ING-MINYAK', 0.015, 'L']],
            'PRD-SNACK' => [['ING-PISANG', 1, 'PCS'], ['ING-SUSU', 1, 'PCS']],
        ];
        foreach ($recipes as $prdCode => $items) {
            $product = Product::where('code', $prdCode)->firstOrFail();
            $recipe = Recipe::firstOrCreate(['code' => 'RCP-'.$prdCode], [
                'organization_id' => $org->id, 'product_id' => $product->id,
                'name' => 'Resep '.$product->name, 'version' => '1.0',
                'yield_qty' => 1, 'yield_unit_id' => $pack->id,
                'instructions' => 'Masak sesuai SOP MBG.', 'cook_time_minutes' => 120, 'is_active' => true,
            ]);
            foreach ($items as [$ingCode, $qty, $unitCode]) {
                $recipe->items()->firstOrCreate(['recipe_id' => $recipe->id, 'ingredient_id' => $byCode($ingCode)->id], [
                    'qty' => $qty, 'unit_id' => Unit::where('code', $unitCode)->firstOrFail()->id, 'waste_factor_pct' => 2,
                ]);
            }
        }

        // ---- Menu minggu ini ----
        $menuProducts = Product::whereIn('code', ['PRD-NASI-AYAM', 'PRD-NASI-TELUR', 'PRD-NASI-TEMPE', 'PRD-SNACK'])->get();
        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
        $pick = [0, 1, 2, 0, 3];
        foreach ($days as $i => $day) {
            $date = now()->startOfWeek()->addDays($i)->toDateString();
            $menu = Menu::firstOrCreate(['code' => 'MNU-'.$date], [
                'organization_id' => $org->id, 'central_kitchen_id' => $ck->id,
                'name' => 'Menu '.$day, 'menu_date' => $date, 'meal_type' => $pick[$i] === 3 ? 'SNACK' : 'LUNCH',
                'planned_portions' => 2675, 'budget_per_portion' => 15000, 'status' => 'APPROVED',
            ]);
            $menu->items()->firstOrCreate(['menu_id' => $menu->id, 'product_id' => $menuProducts[$pick[$i]]->id], ['qty_per_portion' => 1, 'sort_order' => 0]);
        }

        // ---- Users ----
        $admin = User::firstOrCreate(['email' => 'admin@mbg.id'], [
            'organization_id' => $org->id, 'central_kitchen_id' => $ck->id,
            'name' => 'Administrator', 'password' => 'password123', 'is_active' => true,
        ]);
        $admin->assignRole('super-admin');

        $staff = [
            ['procurement@mbg.id', 'Procurement', 'procurement', null],
            ['gudang@mbg.id', 'Gudang', 'warehouse', $whDry->id],
            ['dapur@mbg.id', 'Kepala Dapur', 'kitchen', null],
            ['driver@mbg.id', 'Kurir', 'driver', null],
        ];
        foreach ($staff as [$email, $name, $role, $whId]) {
            $u = User::firstOrCreate(['email' => $email], [
                'organization_id' => $org->id, 'central_kitchen_id' => $ck->id, 'warehouse_id' => $whId,
                'name' => $name, 'password' => 'password123', 'is_active' => true,
            ]);
            $u->assignRole($role);
        }

        // ---- Recipients sample (2 sekolah) ----
        foreach (School::take(2)->get() as $school) {
            if ($school->recipients()->exists()) {
                continue;
            }
            for ($i = 1; $i <= 10; $i++) {
                $school->recipients()->create([
                    'name' => 'Siswa '.$school->code.'-'.$i,
                    'identifier' => 'NIS'.$school->id.str_pad((string) $i, 3, '0', STR_PAD_LEFT),
                    'grade' => (string) (($i % 6) + 1), 'class_name' => chr(65 + ($i % 3)),
                    'gender' => $i % 2 ? 'L' : 'P', 'is_active' => true,
                ]);
            }
        }
    }
}
