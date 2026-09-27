<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesOrgAccess;
use App\Http\Controllers\Concerns\FiltersRequests;
use App\Models\Recipient;
use App\Models\School;
use Illuminate\Http\Request;

class RecipientController extends Controller
{
    use AuthorizesOrgAccess, FiltersRequests;

    public function index(Request $request)
    {
        $query = Recipient::with(['school'])
            ->whereHas('school', fn ($q) => $q->where('organization_id', $request->user()->organization_id))
            ->when($request->user()->central_kitchen_id, fn ($q) => $q->whereHas('school', fn ($s) => $s->where('central_kitchen_id', $request->user()->central_kitchen_id)))
            ->when($request->filled('school_id'), fn ($q) => $q->where('school_id', $request->school_id))
            ->when($request->filled('allergy'), fn ($q) => $q->whereNotNull('allergy_notes')->where('allergy_notes', '!=', ''));
        if ($request->filled('q')) {
            $query->where(fn ($w) => $w->where('name', 'like', '%'.$request->q.'%')->orWhere('identifier', 'like', '%'.$request->q.'%'));
        }
        $recipients = $query->latest()->paginate(20)->withQueryString();
        $schools = School::active()->where('organization_id', $request->user()->organization_id)->get();
        $allergyCount = (clone $query)->whereNotNull('allergy_notes')->where('allergy_notes', '!=', '')->count();

        return view('recipients.index', compact('recipients', 'schools', 'allergyCount'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'school_id' => 'required|exists:schools,id',
            'name' => 'required|string|max:255',
            'identifier' => 'nullable|string|max:50',
            'grade' => 'nullable|string|max:20',
            'class_name' => 'nullable|string|max:20',
            'gender' => 'nullable|in:L,P',
            'allergy_notes' => 'nullable|string',
        ]);
        $school = School::findOrFail($data['school_id']);
        $this->ensureOrgAccess($school);
        $school->recipients()->create($data);

        return back()->with('success', 'Penerima ditambahkan.');
    }

    public function update(Request $request, Recipient $recipient)
    {
        $this->ensureOrgAccess($recipient->school);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'identifier' => 'nullable|string|max:50',
            'grade' => 'nullable|string|max:20',
            'class_name' => 'nullable|string|max:20',
            'gender' => 'nullable|in:L,P',
            'allergy_notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);
        $recipient->update($data);

        return back()->with('success', 'Penerima diperbarui.');
    }

    public function destroy(Recipient $recipient)
    {
        $this->ensureOrgAccess($recipient->school);
        $recipient->delete();

        return back()->with('success', 'Penerima dihapus.');
    }
}
