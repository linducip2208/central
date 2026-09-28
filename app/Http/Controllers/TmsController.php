<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesOrgAccess;
use App\Http\Controllers\Concerns\FiltersRequests;
use App\Models\CapaAction;
use App\Models\Delivery;
use App\Models\DeliveryRoute;
use App\Models\DeliveryRouteStop;
use App\Models\NonConformance;
use App\Models\PurchaseOrder;
use App\Models\School;
use App\Models\SchoolConfirmation;
use App\Models\Vehicle;
use App\Services\GeofenceService;
use App\Services\KpiService;
use App\Services\NumberService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TmsController extends Controller
{
    use AuthorizesOrgAccess, FiltersRequests;

    public function routes(Request $request)
    {
        $query = DeliveryRoute::with(['vehicle', 'driver', 'stops.school'])->where('organization_id', $request->user()->organization_id);
        $this->scopeKitchen($request, $query);
        $routes = $this->tableQuery($request, $query, ['code', 'name']);

        return view('tms.routes', compact('routes'));
    }

    public function storeRoute(Request $request, NumberService $numbers)
    {
        $this->ensureKitchen((int) $request->get('central_kitchen_id'));
        $data = $request->validate([
            'central_kitchen_id' => 'required|exists:central_kitchens,id',
            'name' => 'required|string|max:255',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'driver_id' => 'nullable|exists:users,id',
        ]);
        DeliveryRoute::create([
            'organization_id' => $request->user()->organization_id,
            'central_kitchen_id' => $data['central_kitchen_id'],
            'code' => $numbers->next('RTE'),
            'name' => $data['name'],
            'vehicle_id' => $data['vehicle_id'] ?? null,
            'driver_id' => $data['driver_id'] ?? null,
            'is_active' => true,
        ]);

        return back()->with('success', 'Rute dibuat.');
    }

    public function routeShow(DeliveryRoute $route)
    {
        $this->ensureOrgAccess($route);
        $route->load(['stops.school', 'vehicle', 'driver']);
        $schools = School::active()->where('organization_id', $route->organization_id)->get();

        return view('tms.route-show', compact('route', 'schools'));
    }

    public function storeStop(Request $request, DeliveryRoute $route)
    {
        $this->ensureOrgAccess($route);
        $this->ensureSchool((int) $request->get('school_id'));
        $data = $request->validate([
            'school_id' => 'required|exists:schools,id',
            'sequence' => 'required|integer|min:0',
            'window_start' => 'nullable|date_format:H:i',
            'window_end' => 'nullable|date_format:H:i|after:window_start',
        ]);
        $route->stops()->updateOrCreate(['school_id' => $data['school_id']], $data + ['delivery_route_id' => $route->id]);

        return back()->with('success', 'Stop ditambahkan.');
    }

    public function destroyStop(DeliveryRouteStop $stop)
    {
        $this->ensureOrgAccess($stop->route);
        $stop->delete();

        return back()->with('success', 'Stop dihapus.');
    }

    /** Optimasi urutan stop (nearest-neighbor dari koordinat dapur). */
    public function optimize(DeliveryRoute $route, GeofenceService $geo)
    {
        $this->ensureOrgAccess($route);
        $route->load(['stops.school', 'centralKitchen']);
        $depot = $route->centralKitchen;
        abort_unless($depot?->latitude !== null && $depot?->longitude !== null, 422, 'Isi koordinat dapur dahulu (menu Central Kitchen).');
        $stops = [];
        foreach ($route->stops as $stop) {
            if ($stop->school?->latitude !== null && $stop->school?->longitude !== null) {
                $stops[$stop->id] = ['lat' => (float) $stop->school->latitude, 'lon' => (float) $stop->school->longitude];
            }
        }
        abort_unless(count($stops) >= 2, 422, 'Minimal 2 stop berkoordinat untuk optimasi.');
        $order = $geo->optimizeSequence((float) $depot->latitude, (float) $depot->longitude, $stops);
        foreach (array_values($order) as $i => $stopId) {
            DeliveryRouteStop::whereKey($stopId)->update(['sequence' => $i + 1]);
        }

        return back()->with('success', 'Urutan stop dioptimasi (nearest-neighbor dari dapur).');
    }

    /** Terapkan rute ke delivery: isi vehicle/driver/sequence/ETA per stop. */
    public function applyRoute(Request $request, DeliveryRoute $route)
    {
        $this->ensureOrgAccess($route);
        $request->validate(['distribution_id' => 'nullable|exists:distributions,id']);
        $count = DB::transaction(function () use ($request, $route) {
            $n = 0;
            $eta = now()->addMinutes(30);
            foreach ($route->stops as $stop) {
                $q = Delivery::where('organization_id', $route->organization_id)
                    ->where('school_id', $stop->school_id)
                    ->whereIn('status', ['PLANNED', 'IN_TRANSIT']);
                if ($request->filled('distribution_id')) {
                    $q->where('distribution_id', $request->distribution_id);
                }
                $delivery = $q->latest()->first();
                if (! $delivery) {
                    continue;
                }
                $delivery->update([
                    'delivery_route_id' => $route->id,
                    'vehicle_id' => $route->vehicle_id,
                    'stop_sequence' => $stop->sequence,
                    'courier_id' => $route->driver_id ?? $delivery->courier_id,
                    'eta' => $eta->copy(),
                ]);
                // Cek jendela: tandai bila ETA di luar window.
                $eta->addMinutes(25);
                $n++;
            }

            return $n;
        });

        return back()->with('success', "Rute diterapkan ke {$count} delivery (kendaraan, kurir, urutan, ETA).");
    }

    /** Central Control Tower: delivery + produksi + stok + QC + supplier + biaya. */
    public function controlTower(Request $request, KpiService $kpi)
    {
        $orgId = $request->user()->organization_id;
        $kitchenId = $request->user()->central_kitchen_id;
        $date = $request->get('date', today()->toDateString());

        $query = Delivery::with(['school', 'vehicle', 'courier', 'route'])->where('organization_id', $orgId);
        $this->scopeKitchen($request, $query);
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            $query->whereIn('status', ['PLANNED', 'IN_TRANSIT', 'PARTIAL']);
        }
        $query->whereDate('delivery_date', $date);
        $deliveries = $query->orderBy('stop_sequence')->orderBy('eta')->get();
        $stats = [
            'total' => $deliveries->count(),
            'dispatched' => $deliveries->whereIn('status', ['PLANNED', 'IN_TRANSIT'])->count(),
            'delivered' => $deliveries->where('status', 'DELIVERED')->count(),
            'partial' => $deliveries->where('status', 'PARTIAL')->count(),
            'failed' => $deliveries->where('status', 'FAILED')->count(),
            'late' => $deliveries->filter(fn ($d) => $d->isLate())->count(),
            'no_pod' => $deliveries->whereNull('delivery_proof')->count(),
        ];

        $tower = $kpi->controlTower($orgId, $kitchenId);
        $lateDeliveries = $deliveries->filter(fn ($d) => $d->isLate())->take(10);
        $failedDeliveries = Delivery::with(['school'])->where('organization_id', $orgId)
            ->when($kitchenId, fn ($q) => $q->where('central_kitchen_id', $kitchenId))
            ->whereDate('delivery_date', $date)->where('status', 'FAILED')->take(10)->get();
        $openNcrs = NonConformance::with(['batch'])->where('organization_id', $orgId)
            ->when($kitchenId, fn ($q) => $q->where('central_kitchen_id', $kitchenId))
            ->where('status', 'OPEN')->latest()->take(10)->get();
        $overdueCapas = CapaAction::with(['nonConformance'])->where('status', '!=', 'DONE')
            ->whereDate('due_date', '<', $date)
            ->whereHas('nonConformance', fn ($q) => $q->where('organization_id', $orgId))
            ->take(10)->get();
        $pendingPos = PurchaseOrder::with(['supplier'])->where('organization_id', $orgId)
            ->when($kitchenId, fn ($q) => $q->where('central_kitchen_id', $kitchenId))
            ->whereIn('status', ['SUBMITTED', 'DRAFT'])->latest()->take(10)->get();
        $recentComplaints = SchoolConfirmation::with(['school'])->whereNotNull('complaint')->where('complaint', '!=', '')
            ->whereHas('school', fn ($q) => $q->where('organization_id', $orgId))
            ->latest()->take(10)->get();

        return view('tms.tower', compact('deliveries', 'stats', 'tower', 'lateDeliveries', 'failedDeliveries', 'openNcrs', 'overdueCapas', 'pendingPos', 'recentComplaints', 'date'));
    }
}
