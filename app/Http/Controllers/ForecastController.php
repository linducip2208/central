<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesOrgAccess;
use App\Models\CentralKitchen;
use App\Models\DeliveryItem;
use App\Models\DemandForecast;
use App\Models\Product;
use App\Services\ForecastService;
use Illuminate\Http\Request;

class ForecastController extends Controller
{
    use AuthorizesOrgAccess;

    public function index(Request $request)
    {
        $products = Product::active()->where('organization_id', $request->user()->organization_id)->get();
        $forecasts = DemandForecast::with(['product'])
            ->where('organization_id', $request->user()->organization_id)
            ->latest()->take(30)->get();
        $avgError = DemandForecast::where('organization_id', $request->user()->organization_id)
            ->whereNotNull('error_pct')->avg('error_pct');

        return view('forecasts.index', compact('products', 'forecasts', 'avgError'));
    }

    /** Generate forecast versi baru (moving average 14 hari × horizon). */
    public function generate(Request $request, ForecastService $forecast)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'horizon_days' => 'required|integer|min:1|max:90',
        ]);
        $product = Product::findOrFail($data['product_id']);
        $this->ensureOrgAccess($product);
        $daily = $forecast->forecastProductDemand($product->id, 14);
        $start = now()->toDateString();
        $end = now()->addDays($data['horizon_days'] - 1)->toDateString();
        $version = (int) (DemandForecast::where('organization_id', $product->organization_id)->where('product_id', $product->id)->max('version') ?? 0) + 1;
        DemandForecast::create([
            'organization_id' => $product->organization_id,
            'central_kitchen_id' => $request->user()->central_kitchen_id ?? CentralKitchen::first()->id,
            'product_id' => $product->id,
            'period_start' => $start, 'period_end' => $end,
            'version' => $version, 'method' => 'MOVING_AVG',
            'confidence' => 0.7,
            'forecast_qty' => round($daily * $data['horizon_days']),
            'status' => 'APPROVED',
            'created_by' => $request->user()->id,
        ]);

        return back()->with('success', "Forecast v{$version} dibuat: {$daily}/hari × {$data['horizon_days']} hari.");
    }

    /** Catat aktual dari delivery pada periode → hitung MAPE. */
    public function recordActual(DemandForecast $forecast)
    {
        $this->ensureOrgAccess($forecast);
        $actual = (float) DeliveryItem::where('product_id', $forecast->product_id)
            ->whereHas('delivery', fn ($q) => $q->whereBetween('delivery_date', [$forecast->period_start, $forecast->period_end]))
            ->sum('qty_delivered');
        $forecast->update(['actual_qty' => $actual, 'status' => 'CLOSED']);

        return back()->with('success', "Aktual tercatat: {$actual}. MAPE: {$forecast->fresh()->error_pct}%.");
    }

    /** What-if scenario: clone forecast × faktor. */
    public function scenario(Request $request, DemandForecast $forecast)
    {
        $this->ensureOrgAccess($forecast);
        $request->validate(['factor' => 'required|numeric|min:0.1|max:5', 'name' => 'required|string|max:80']);
        $copy = $forecast->replicate(['actual_qty', 'error_pct']);
        $copy->forecast_qty = round((float) $forecast->forecast_qty * (float) $request->factor);
        $copy->is_scenario = true;
        $copy->scenario_name = $request->name;
        $copy->scenario_factor = $request->factor;
        $copy->status = 'DRAFT';
        $copy->save();

        return back()->with('success', "Skenario '{$copy->scenario_name}' dibuat: {$copy->forecast_qty} (×{$copy->scenario_factor}).");
    }
}
