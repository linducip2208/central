<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Allergen;
use App\Models\Recipient;
use App\Models\School;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function schools(Request $request)
    {
        $schools = School::where('organization_id', $request->user()->organization_id)
            ->when($request->user()->central_kitchen_id, fn ($q) => $q->where('central_kitchen_id', $request->user()->central_kitchen_id))
            ->when($request->filled('q'), fn ($q) => $q->where('name', 'like', '%'.$request->q.'%'))
            ->paginate(20);

        return response()->json($schools);
    }

    public function schoolShow(Request $request, School $school)
    {
        abort_unless((int) $school->organization_id === (int) $request->user()->organization_id, 403);
        $school->load(['recipients' => fn ($q) => $q->take(50)]);

        return response()->json($school);
    }

    public function recipients(Request $request)
    {
        $recipients = Recipient::with(['school', 'allergens'])
            ->whereHas('school', fn ($q) => $q->where('organization_id', $request->user()->organization_id))
            ->when($request->filled('school_id'), fn ($q) => $q->where('school_id', $request->school_id))
            ->when($request->boolean('allergy'), fn ($q) => $q->whereHas('allergens'))
            ->paginate(30);

        return response()->json($recipients);
    }

    public function allergens()
    {
        return response()->json(['data' => Allergen::all()]);
    }
}
