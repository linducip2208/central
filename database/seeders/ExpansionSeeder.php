<?php

namespace Database\Seeders;

use App\Core\Services\SettingService;
use App\Models\Allergen;
use App\Models\CentralKitchen;
use App\Models\DeliveryRoute;
use App\Models\FeatureFlag;
use App\Models\Ingredient;
use App\Models\InspectionTemplate;
use App\Models\MealGroup;
use App\Models\Menu;
use App\Models\MenuCycle;
use App\Models\Organization;
use App\Models\School;
use App\Models\Supplier;
use App\Models\SupplierPriceList;
use App\Models\Unit;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Warehouse;
use App\Models\WarehouseBin;
use App\Models\WarehouseRack;
use App\Models\WarehouseZone;
use App\Models\WorkCenter;
use Illuminate\Database\Seeder;

class ExpansionSeeder extends Seeder
{
    public function run(): void
    {
        $org = Organization::where('code', 'MBG-01')->first() ?? Organization::first();
        $ck = CentralKitchen::where('organization_id', $org->id)->first();
        if (! $org || ! $ck) {
            return;
        }

        // Alergen + tautan bahan umum.
        $allergenMap = [
            'SUSU' => ['Susu sapi & olahannya', ['ING-SUSU']],
            'TELUR' => ['Telur & olahannya', ['ING-TELUR']],
            'KEDELAI' => ['Kedelai: tahu, tempe', ['ING-TAHU', 'ING-TEMPE']],
            'GLUTEN' => ['Gluten/gandum', []],
            'KACANG' => ['Kacang tanah & tree nuts', []],
            'IKAN' => ['Ikan & seafood', []],
        ];
        foreach ($allergenMap as $code => [$name, $ings]) {
            $a = Allergen::firstOrCreate(['code' => $code], ['name' => $name]);
            foreach ($ings as $ic) {
                $ing = Ingredient::where('code', $ic)->first();
                if ($ing) {
                    $a->ingredients()->syncWithoutDetaching([$ing->id]);
                }
            }
        }

        MealGroup::firstOrCreate(['code' => 'REGULER'], ['organization_id' => $org->id, 'name' => 'Reguler', 'is_active' => true]);
        MealGroup::firstOrCreate(['code' => 'VEGETARIAN'], ['organization_id' => $org->id, 'name' => 'Vegetarian', 'dietary_notes' => 'Tanpa daging/ayam/ikan', 'is_active' => true]);

        // Koordinat demo: dapur di Monas, sekolah menyebar (untuk optimasi + geofence).
        $ck->update(['latitude' => -6.1754, 'longitude' => 106.8272]);
        $coords = [[-6.1954, 106.8239], [-6.2146, 106.8451], [-6.1692, 106.8319], [-6.2297, 106.8294], [-6.1865, 106.8003]];
        $si = 0;
        foreach (School::where('central_kitchen_id', $ck->id)->take(5)->get() as $school) {
            $school->update(['latitude' => $coords[$si][0], 'longitude' => $coords[$si][1], 'geofence_radius_m' => 500]);
            $si++;
        }

        // Kendaraan + rute + stop.
        $v = Vehicle::firstOrCreate(['plate_no' => 'B 1234 MBG'], [
            'organization_id' => $org->id, 'central_kitchen_id' => $ck->id,
            'name' => 'Box Pendingin 1', 'vehicle_type' => 'BOX', 'capacity_portions' => 1500, 'has_cooler' => true, 'status' => 'ACTIVE',
        ]);
        $driver = User::where('email', 'driver@mbg.id')->first();
        $route = DeliveryRoute::firstOrCreate(['code' => 'RTE-01'], [
            'organization_id' => $org->id, 'central_kitchen_id' => $ck->id,
            'name' => 'Rute Timur Pagi', 'vehicle_id' => $v->id, 'driver_id' => $driver?->id, 'is_active' => true,
        ]);
        $seq = 1;
        foreach (School::where('central_kitchen_id', $ck->id)->take(5)->get() as $school) {
            $route->stops()->firstOrCreate(['school_id' => $school->id], ['sequence' => $seq++, 'window_start' => '07:00', 'window_end' => '09:00']);
        }

        // Lokasi WMS gudang kering.
        $wh = Warehouse::where('code', 'WH-DRY-01')->first();
        if ($wh) {
            $zone = WarehouseZone::firstOrCreate(['warehouse_id' => $wh->id, 'code' => 'A'], ['name' => 'Zona A Kering', 'zone_type' => 'STORAGE']);
            $rack = WarehouseRack::firstOrCreate(['warehouse_zone_id' => $zone->id, 'code' => 'R01'], ['name' => 'Rak 01']);
            foreach (['B01', 'B02', 'B03', 'B04'] as $binCode) {
                WarehouseBin::firstOrCreate(
                    ['warehouse_rack_id' => $rack->id, 'code' => $binCode],
                    ['barcode' => 'A-R01-'.$binCode, 'is_active' => true]
                );
            }
        }

        // Work centers.
        foreach ([['WC-MASAK', 'Tungku Masak', 'COOKING', 800], ['WC-PACK', 'Meja Packing', 'PACKING', 1200], ['WC-CUCI', 'Pencucian', 'WASHING', 0]] as [$code, $name, $type, $cap]) {
            WorkCenter::firstOrCreate(['code' => $code], ['central_kitchen_id' => $ck->id, 'name' => $name, 'center_type' => $type, 'capacity_per_hour' => $cap, 'operators_required' => 2, 'status' => 'ACTIVE']);
        }

        // Template inspeksi.
        $templates = [
            ['QCT-INCOMING', 'Incoming Material', 'INCOMING', [
                ['name' => 'Kondisi kemasan', 'spec_min' => null, 'spec_max' => null, 'unit' => 'visual'],
                ['name' => 'Suhu terima dingin (°C)', 'spec_min' => 0, 'spec_max' => 4, 'unit' => '°C'],
                ['name' => 'Sisa expired (hari)', 'spec_min' => 30, 'spec_max' => null, 'unit' => 'hari'],
            ]],
            ['QCT-MASAK', 'Suhu Masak', 'IN_PROCESS', [
                ['name' => 'Suhu inti (°C)', 'spec_min' => 75, 'spec_max' => null, 'unit' => '°C'],
            ]],
            ['QCT-FINISH', 'Organoleptik Akhir', 'FINISHED', [
                ['name' => 'Rasa (1-5)', 'spec_min' => 4, 'spec_max' => 5, 'unit' => 'skor'],
                ['name' => 'Suhu saji (°C)', 'spec_min' => 60, 'spec_max' => null, 'unit' => '°C'],
            ]],
        ];
        foreach ($templates as [$code, $name, $stage, $params]) {
            InspectionTemplate::firstOrCreate(['code' => $code], ['organization_id' => $org->id, 'name' => $name, 'stage' => $stage, 'parameters' => $params, 'is_active' => true]);
        }

        // Price list supplier utama dari harga standar (untuk MRP/award).
        $sup = Supplier::where('code', 'SUP-BERAS')->first();
        if ($sup) {
            foreach (Ingredient::where('organization_id', $org->id)->take(10)->get() as $ing) {
                SupplierPriceList::firstOrCreate(
                    ['supplier_id' => $sup->id, 'ingredient_id' => $ing->id],
                    ['price' => $ing->standard_price, 'unit_id' => $ing->unit_id, 'moq' => 10, 'lead_time_days' => 1]
                );
            }
        }

        // Bahan kemasan.
        $pcs = Unit::where('code', 'PCS')->first();
        Ingredient::firstOrCreate(['code' => 'ING-BOX'], [
            'organization_id' => $org->id, 'name' => 'Box Makanan', 'category' => 'PACKAGING',
            'unit_id' => $pcs->id, 'standard_price' => 1500, 'min_stock' => 500, 'max_stock' => 10000, 'is_active' => true,
        ]);

        // Siklus menu 5 hari.
        $cycle = MenuCycle::firstOrCreate(['code' => 'MCY-01'], [
            'organization_id' => $org->id, 'name' => 'Siklus Mingguan', 'cycle_days' => 5,
            'start_date' => now()->startOfWeek()->toDateString(), 'status' => 'DRAFT',
        ]);
        $menus = Menu::where('organization_id', $org->id)->orderBy('menu_date')->take(5)->get();
        foreach ($menus->values() as $i => $menu) {
            $cycle->days()->firstOrCreate(['day_no' => $i + 1], ['menu_id' => $menu->id]);
        }

        // Settings pajak & currency + feature flags.
        $settings = app(SettingService::class);
        foreach (['tax.ppn_pct' => 11, 'currency.default' => 'IDR', 'delivery.max_temp_c' => 10, 'qc.photo_required' => true] as $k => $val) {
            if (! $settings->has($k)) {
                $settings->set($k, $val);
            }
        }
        foreach (['portal.enabled' => 'Portal sekolah aktif', 'api.v2' => 'API v2 (roadmap, nonaktif)', 'ai.advisor' => 'Advisor deterministik aktif'] as $k => $desc) {
            FeatureFlag::firstOrCreate(['key' => $k], ['is_enabled' => $k !== 'api.v2', 'description' => $desc]);
        }

        // User portal sekolah.
        $schoolUser = User::firstOrCreate(['email' => 'sekolah@mbg.id'], [
            'organization_id' => $org->id, 'central_kitchen_id' => $ck->id,
            'name' => 'Operator Sekolah', 'password' => 'password123', 'is_active' => true,
        ]);
        $schoolUser->assignRole('school');
    }
}
