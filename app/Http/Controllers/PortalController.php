<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesOrgAccess;
use App\Models\Delivery;
use App\Models\School;
use App\Models\SchoolConfirmation;
use Illuminate\Http\Request;

class PortalController extends Controller
{
    use AuthorizesOrgAccess;

    protected function schools(Request $request)
    {
        $q = School::active()->where('organization_id', $request->user()->organization_id);
        if ($request->user()->central_kitchen_id) {
            $q->where('central_kitchen_id', $request->user()->central_kitchen_id);
        }

        return $q->get();
    }

    public function index(Request $request)
    {
        $schoolIds = $this->schools($request)->pluck('id');
        $deliveries = Delivery::with(['school', 'confirmation'])
            ->whereIn('school_id', $schoolIds)
            ->whereIn('status', ['DELIVERED', 'PARTIAL', 'IN_TRANSIT'])
            ->latest('delivery_date')->take(30)->get();
        $pending = (clone $deliveries)->filter(fn ($d) => ! $d->confirmation);

        return view('portal.index', compact('deliveries', 'pending'));
    }

    public function show(Request $request, Delivery $delivery)
    {
        abort_unless($this->schools($request)->pluck('id')->contains($delivery->school_id), 403);
        $delivery->load(['items.product', 'school', 'confirmation']);

        return view('portal.show', compact('delivery'));
    }

    public function confirm(Request $request, Delivery $delivery)
    {
        abort_unless($this->schools($request)->pluck('id')->contains($delivery->school_id), 403);
        $data = $request->validate([
            'received_qty' => 'required|integer|min:0|max:'.$delivery->qty_planned,
            'rejected_qty' => 'nullable|integer|min:0',
            'attendance' => 'nullable|integer|min:0',
            'complaint' => 'nullable|string',
            'feedback' => 'nullable|string',
        ]);
        SchoolConfirmation::updateOrCreate(
            ['delivery_id' => $delivery->id, 'school_id' => $delivery->school_id],
            [
                'expected_qty' => $delivery->qty_planned,
                'received_qty' => $data['received_qty'],
                'rejected_qty' => $data['rejected_qty'] ?? 0,
                'attendance' => $data['attendance'] ?? 0,
                'complaint' => $data['complaint'] ?? null,
                'feedback' => $data['feedback'] ?? null,
                'status' => 'SUBMITTED',
                'confirmed_by' => $request->user()->id,
            ]
        );

        return back()->with('success', 'Konfirmasi tersimpan. Terima kasih.');
    }

    public function complaints(Request $request)
    {
        $schoolIds = $this->schools($request)->pluck('id');
        $confirmations = SchoolConfirmation::with(['school', 'delivery'])
            ->whereIn('school_id', $schoolIds)
            ->whereNotNull('complaint')->where('complaint', '!=', '')
            ->latest()->paginate(20);

        return view('portal.complaints', compact('confirmations'));
    }
}
