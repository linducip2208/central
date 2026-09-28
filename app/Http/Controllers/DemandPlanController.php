<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesOrgAccess;
use App\Http\Controllers\Concerns\FiltersRequests;
use App\Models\CentralKitchen;
use App\Models\DemandPlan;
use App\Models\DemandPlanLine;
use App\Models\Menu;
use App\Models\School;
use App\Services\ForecastService;
use App\Services\NumberService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DemandPlanController extends Controller
{
    use AuthorizesOrgAccess, FiltersRequests;

    public function index(Request $request)
    {
        $query = DemandPlan::where('organization_id', $request->user()->organization_id);
        $this->scopeKitchen($request, $query);
        $plans = $this->tableQuery($request, $query, ['number']);

        return view('demand-plans.index', compact('plans'));
    }

    public function create(Request $request)
    {
        $kitchens = CentralKitchen::active()->where('organization_id', $request->user()->organization_id)->get();
        $menus = Menu::where('organization_id', $request->user()->organization_id)->where('status', 'APPROVED')->latest()->take(60)->get();
        $schools = School::active()->where('organization_id', $request->user()->organization_id)->get();

        return view('demand-plans.form', ['plan' => new DemandPlan, 'kitchens' => $kitchens, 'menus' => $menus, 'schools' => $schools]);
    }

    /**
     * Generate demand plan: gross per sekolah (target porsi × hari),
     * dikurangi ketidakhadiran, + penyesuaian manual + safety stock.
     */
    public function store(Request $request, NumberService $numbers, ForecastService $forecast)
    {
        $data = $request->validate([
            'central_kitchen_id' => 'required|exists:central_kitchens,id',
            'period_type' => 'required|in:DAILY,WEEKLY,MONTHLY',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'menu_id' => 'nullable|exists:menus,id',
            'school_ids' => 'required|array|min:1',
            'school_ids.*' => 'exists:schools,id',
            'attendance_pct' => 'nullable|numeric|min:0|max:100',
            'safety_pct' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string',
        ]);

        $this->ensureKitchen((int) $data['central_kitchen_id']);
        $schoolCount = School::where('organization_id', $request->user()->organization_id)->whereIn('id', $data['school_ids'])->count();
        abort_unless($schoolCount === count($data['school_ids']), 403, 'Sekolah di luar organisasi Anda.');
        $plan = DB::transaction(function () use ($request, $data, $numbers) {
            $plan = DemandPlan::create([
                'organization_id' => $request->user()->organization_id,
                'central_kitchen_id' => $data['central_kitchen_id'],
                'number' => $numbers->next('DP'),
                'period_type' => $data['period_type'],
                'period_start' => $data['period_start'],
                'period_end' => $data['period_end'],
                'status' => 'DRAFT',
                'notes' => $data['notes'] ?? null,
                'created_by' => $request->user()->id,
            ]);
            $attendancePct = $data['attendance_pct'] ?? 95;
            $safetyPct = $data['safety_pct'] ?? 5;
            $cursor = Carbon::parse($data['period_start']);
            $end = Carbon::parse($data['period_end']);
            $schools = School::whereIn('id', $data['school_ids'])->get();
            $lineCount = 0;
            while ($cursor->lte($end) && $lineCount <= 2000) {
                if (! ($cursor->isWeekend() && $data['period_type'] !== 'DAILY')) {
                    foreach ($schools as $school) {
                        $gross = (int) $school->target_portions;
                        $absent = (int) round($gross * (100 - $attendancePct) / 100);
                        $safety = (int) round($gross * $safetyPct / 100);
                        $plan->lines()->create([
                            'school_id' => $school->id, 'menu_id' => $data['menu_id'] ?? null,
                            'demand_date' => $cursor->toDateString(),
                            'gross_demand' => $gross, 'attendance_adjustment' => $absent,
                            'manual_adjustment' => 0, 'safety_stock' => $safety,
                            'source' => 'FORECAST',
                        ]);
                        $lineCount++;
                        if ($lineCount > 2000) {
                            break; // guard periode raksasa
                        }
                    }
                }
                $cursor->addDay();
            }

            return $plan;
        });

        return redirect()->route('demand-plans.show', $plan)->with('success', 'Demand plan dibuat: '.$plan->lines()->count().' baris.');
    }

    public function show(DemandPlan $plan)
    {
        $this->ensureOrgAccess($plan);
        $plan->load(['lines.school', 'lines.menu']);
        $summary = [
            'gross' => $plan->lines()->sum('gross_demand'),
            'adjusted' => $plan->lines()->sum('adjusted_demand'),
            'net' => $plan->lines()->sum('net_demand'),
        ];
        $lines = $plan->lines()->with(['school', 'menu'])->orderBy('demand_date')->paginate(30);

        return view('demand-plans.show', compact('plan', 'summary', 'lines'));
    }

    public function approve(Request $request, DemandPlan $plan)
    {
        $this->ensureOrgAccess($plan);
        abort_unless($plan->status === 'DRAFT', 422);
        $plan->update(['status' => 'APPROVED']);

        return back()->with('success', 'Demand plan disetujui. Jalankan MRP dari halaman ini.');
    }

    public function updateLine(Request $request, DemandPlan $plan, DemandPlanLine $line)
    {
        $this->ensureOrgAccess($plan);
        abort_unless($plan->status === 'DRAFT', 422, 'Hanya plan DRAFT yang dapat disesuaikan.');
        abort_unless($line->demand_plan_id === $plan->id, 422);
        $data = $request->validate([
            'attendance_adjustment' => 'nullable|integer|min:0',
            'manual_adjustment' => 'nullable|integer',
            'safety_stock' => 'nullable|integer|min:0',
        ]);
        $line->update(array_filter($data, fn ($v) => $v !== null));

        return back()->with('success', 'Baris demand disesuaikan (net dihitung ulang).');
    }
}
