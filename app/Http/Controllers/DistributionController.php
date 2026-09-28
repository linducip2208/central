<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesOrgAccess;
use App\Http\Controllers\Concerns\FiltersRequests;
use App\Models\CentralKitchen;
use App\Models\Delivery;
use App\Models\Distribution;
use App\Models\Packaging;
use App\Models\Product;
use App\Models\School;
use App\Services\NumberService;
use App\Services\WebhookService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DistributionController extends Controller
{
    use AuthorizesOrgAccess, FiltersRequests;

    public function index(Request $request)
    {
        $query = Distribution::where('organization_id', $request->user()->organization_id);
        $this->scopeKitchen($request, $query);
        $dists = $this->tableQuery($request, $query, ['number', 'driver_name', 'vehicle_no']);

        return view('distributions.index', compact('dists'));
    }

    public function create(Request $request)
    {
        $packagings = Packaging::where('organization_id', $request->user()->organization_id)->where('status', 'COMPLETED')->latest()->take(30)->get();
        $schools = School::active()->where('organization_id', $request->user()->organization_id)->get();
        $products = Product::active()->where('organization_id', $request->user()->organization_id)->get();
        $kitchens = CentralKitchen::active()->where('organization_id', $request->user()->organization_id)->get();

        return view('distributions.form', ['dist' => new Distribution, 'packagings' => $packagings, 'schools' => $schools, 'products' => $products, 'kitchens' => $kitchens]);
    }

    public function store(Request $request, NumberService $numbers)
    {
        $data = $request->validate([
            'central_kitchen_id' => 'required|exists:central_kitchens,id',
            'packaging_id' => 'nullable|exists:packagings,id',
            'vehicle_no' => 'nullable|string|max:30', 'driver_name' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.school_id' => 'required|exists:schools,id',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
        ]);
        $this->ensureKitchen((int) $data['central_kitchen_id']);
        if (! empty($data['packaging_id'])) {
            $this->ensureOrgAccess(Packaging::findOrFail($data['packaging_id']));
        }
        foreach ($data['items'] as $it) {
            $this->ensureSchool((int) $it['school_id']);
            $this->ensureOrgAccess(Product::findOrFail($it['product_id']));
        }

        $dist = DB::transaction(function () use ($request, $data, $numbers) {
            $dist = Distribution::create([
                'organization_id' => $request->user()->organization_id,
                'central_kitchen_id' => $data['central_kitchen_id'],
                'packaging_id' => $data['packaging_id'] ?? null,
                'number' => $numbers->next('DST'),
                'distribution_date' => now()->toDateString(),
                'total_portions' => array_sum(array_column($data['items'], 'qty')),
                'vehicle_no' => $data['vehicle_no'] ?? null,
                'driver_name' => $data['driver_name'] ?? null,
                'status' => 'PLANNED',
                'notes' => $data['notes'] ?? null,
                'created_by' => $request->user()->id,
            ]);
            foreach ($data['items'] as $it) {
                $item = $dist->items()->create(['school_id' => $it['school_id'], 'product_id' => $it['product_id'], 'qty_planned' => $it['qty']]);
                // Satu delivery per sekolah per distribusi
                $delivery = Delivery::where('distribution_id', $dist->id)->where('school_id', $it['school_id'])->first();
                if (! $delivery) {
                    $delivery = Delivery::create([
                        'organization_id' => $dist->organization_id,
                        'central_kitchen_id' => $dist->central_kitchen_id,
                        'distribution_id' => $dist->id,
                        'school_id' => $it['school_id'],
                        'number' => $numbers->next('DLV'),
                        'delivery_date' => $dist->distribution_date,
                        'status' => 'PLANNED',
                        'courier_id' => $request->user()->id,
                    ]);
                    $delivery->trackings()->create(['status' => 'PLANNED', 'notes' => 'Delivery dibuat', 'created_by' => $request->user()->id]);
                }
                $delivery->items()->create(['product_id' => $it['product_id'], 'qty_planned' => $it['qty']]);
                $delivery->increment('qty_planned', $it['qty']);
            }

            return $dist;
        });

        return redirect()->route('distributions.show', $dist)->with('success', 'Distribusi dibuat + delivery per sekolah.');
    }

    public function show(Distribution $dist)
    {
        $this->ensureOrgAccess($dist);
        $dist->load(['items.school', 'items.product', 'deliveries.school']);

        return view('distributions.show', compact('dist'));
    }

    public function dispatch(Distribution $dist)
    {
        $this->ensureOrgAccess($dist);
        abort_unless($dist->status === 'PLANNED', 422);
        DB::transaction(function () use ($dist) {
            $dist->update(['status' => 'IN_TRANSIT']);
            foreach ($dist->deliveries as $d) {
                if ($d->status === 'PLANNED') {
                    $d->update(['status' => 'IN_TRANSIT', 'dispatched_at' => now()]);
                    $d->trackings()->create(['status' => 'IN_TRANSIT', 'notes' => 'Armada berangkat']);
                    app(WebhookService::class)->dispatch('delivery.dispatched', ['number' => $d->number, 'school' => $d->school->name ?? ''], $d->organization_id);
                }
            }
        });

        return back()->with('success', 'Distribusi diberangkatkan.');
    }
}
