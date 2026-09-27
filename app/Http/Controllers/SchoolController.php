<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesOrgAccess;
use App\Http\Controllers\Concerns\FiltersRequests;
use App\Models\CentralKitchen;
use App\Models\Recipient;
use App\Models\School;
use Illuminate\Http\Request;

class SchoolController extends Controller
{
    use AuthorizesOrgAccess, FiltersRequests;

    public function index(Request $request)
    {
        $query = School::with(['centralKitchen'])->where('organization_id', $request->user()->organization_id);
        $this->scopeKitchen($request, $query);
        $schools = $this->tableQuery($request, $query, ['name', 'code', 'npsn']);

        return view('schools.index', compact('schools'));
    }

    public function create(Request $request)
    {
        $kitchens = CentralKitchen::active()->where('organization_id', $request->user()->organization_id)->get();

        return view('schools.form', ['school' => new School, 'kitchens' => $kitchens]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'central_kitchen_id' => 'nullable|exists:central_kitchens,id',
            'npsn' => 'nullable|string|max:30|unique:schools,npsn',
            'name' => 'required|string|max:255',
            'level' => 'required|in:PAUD,TK,SD,SMP,SMA,SMK,SLB',
            'address' => 'nullable|string', 'district' => 'nullable|string|max:100', 'city' => 'nullable|string|max:100',
            'pic_name' => 'nullable|string|max:255', 'pic_phone' => 'nullable|string|max:30',
            'student_count' => 'required|integer|min:0', 'target_portions' => 'required|integer|min:0',
            'distance_km' => 'nullable|numeric|min:0',
            'status' => 'required|in:ACTIVE,INACTIVE',
        ]);
        $data['organization_id'] = $request->user()->organization_id;
        $data['code'] = 'SCH-'.now()->format('ymd').'-'.strtoupper(substr(uniqid(), -4));
        $school = School::create($data);

        return redirect()->route('schools.show', $school)->with('success', 'Sekolah berhasil ditambahkan.');
    }

    public function show(School $school)
    {
        $this->ensureOrgAccess($school);
        $school->load(['recipients' => fn ($q) => $q->latest()->take(20), 'deliveries' => fn ($q) => $q->latest()->take(10)]);

        return view('schools.show', compact('school'));
    }

    public function edit(Request $request, School $school)
    {
        $this->ensureOrgAccess($school);
        $kitchens = CentralKitchen::active()->where('organization_id', $request->user()->organization_id)->get();

        return view('schools.form', compact('school', 'kitchens'));
    }

    public function update(Request $request, School $school)
    {
        $this->ensureOrgAccess($school);
        $data = $request->validate([
            'central_kitchen_id' => 'nullable|exists:central_kitchens,id',
            'npsn' => 'nullable|string|max:30|unique:schools,npsn,'.$school->id,
            'name' => 'required|string|max:255',
            'level' => 'required|in:PAUD,TK,SD,SMP,SMA,SMK,SLB',
            'address' => 'nullable|string', 'district' => 'nullable|string|max:100', 'city' => 'nullable|string|max:100',
            'pic_name' => 'nullable|string|max:255', 'pic_phone' => 'nullable|string|max:30',
            'student_count' => 'required|integer|min:0', 'target_portions' => 'required|integer|min:0',
            'distance_km' => 'nullable|numeric|min:0',
            'status' => 'required|in:ACTIVE,INACTIVE',
        ]);
        $school->update($data);

        return redirect()->route('schools.show', $school)->with('success', 'Sekolah diperbarui.');
    }

    public function destroy(School $school)
    {
        $this->ensureOrgAccess($school);
        if ($school->deliveries()->exists()) {
            return back()->with('error', 'Sekolah memiliki riwayat pengiriman, tidak dapat dihapus.');
        }
        $school->delete();

        return redirect()->route('schools.index')->with('success', 'Sekolah dihapus.');
    }

    public function storeRecipient(Request $request, School $school)
    {
        $this->ensureOrgAccess($school);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'identifier' => 'nullable|string|max:50',
            'grade' => 'nullable|string|max:20', 'class_name' => 'nullable|string|max:20',
            'gender' => 'nullable|in:L,P', 'allergy_notes' => 'nullable|string',
        ]);
        $school->recipients()->create($data);

        return back()->with('success', 'Penerima ditambahkan.');
    }

    public function destroyRecipient(Recipient $recipient)
    {
        $recipient->delete();

        return back()->with('success', 'Penerima dihapus.');
    }
}
