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

    /** Import CSV: school_code,name,identifier,grade,class,gender,allergy. */
    public function import(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:csv,txt|max:2048']);
        $path = $request->file('file')->getRealPath();
        $handle = fopen($path, 'r');
        $header = fgetcsv($handle);
        $expected = ['school_code', 'name', 'identifier', 'grade', 'class', 'gender', 'allergy'];
        if (array_map('strtolower', $header ?? []) !== $expected) {
            fclose($handle);

            return back()->with('error', 'Header harus: '.implode(',', $expected));
        }
        $ok = 0;
        $errors = [];
        $rowNo = 1;
        while (($row = fgetcsv($handle)) !== false) {
            $rowNo++;
            if (count($row) < 2 || trim($row[1] ?? '') === '') {
                continue;
            }
            $school = School::where('code', trim($row[0]))->first();
            if (! $school || (int) $school->organization_id !== (int) $request->user()->organization_id) {
                $errors[] = "Baris {$rowNo}: sekolah tidak dikenal.";

                continue;
            }
            $gender = strtoupper(trim($row[5] ?? ''));
            $school->recipients()->create([
                'name' => trim($row[1]), 'identifier' => trim($row[2] ?? '') ?: null,
                'grade' => trim($row[3] ?? '') ?: null, 'class_name' => trim($row[4] ?? '') ?: null,
                'gender' => in_array($gender, ['L', 'P']) ? $gender : null,
                'allergy_notes' => trim($row[6] ?? '') ?: null, 'is_active' => true,
            ]);
            $ok++;
            if ($ok >= 2000) {
                break;
            }
        }
        fclose($handle);

        return back()->with('success', "Import selesai: {$ok} penerima.".($errors ? ' Gagal: '.implode(' ', array_slice($errors, 0, 5)) : ''));
    }
}
