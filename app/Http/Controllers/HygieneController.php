<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesOrgAccess;
use App\Http\Controllers\Concerns\FiltersRequests;
use App\Models\CentralKitchen;
use App\Models\HygieneCheck;
use Illuminate\Http\Request;

class HygieneController extends Controller
{
    use AuthorizesOrgAccess, FiltersRequests;

    public const CHECKLIST = [
        'CLEANING' => ['Lantai bersih & kering', 'Meja kerja disanitasi', 'Peralatan dicuci + tiris', 'Tempat sampah tertutup & kosong', 'Saluran air lancar'],
        'SANITATION' => ['Tangan dicuci (20 dtk)', 'Sarung tangan + masker dipakai', 'Celemek bersih', 'Tidak ada perhiasan/longgar', 'Suhu badan < 37.5°C'],
        'EQUIPMENT' => ['Kompor/api normal', 'Kulkas/freezer menyala', 'Termometer terkalibrasi', 'Timbangan akurat', 'APAR tersedia & seal utuh'],
    ];

    public function index(Request $request)
    {
        $query = HygieneCheck::where('organization_id', $request->user()->organization_id);
        $this->scopeKitchen($request, $query);
        if ($request->filled('type')) {
            $query->where('check_type', $request->type);
        }
        $checks = $query->latest('checked_at')->paginate(20)->withQueryString();

        return view('hygiene.index', compact('checks'));
    }

    public function create()
    {
        return view('hygiene.form', ['types' => array_keys(self::CHECKLIST)]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'check_type' => 'required|in:CLEANING,SANITATION,EQUIPMENT',
            'area' => 'required|string|max:80',
            'passed' => 'nullable|array',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'notes' => 'nullable|string',
        ]);
        $template = self::CHECKLIST[$data['check_type']];
        $passed = $data['passed'] ?? [];
        $items = [];
        $failed = 0;
        foreach ($template as $i => $label) {
            $ok = in_array((string) $i, array_map('strval', $passed));
            if (! $ok) {
                $failed++;
            }
            $items[] = ['item' => $label, 'pass' => $ok];
        }
        $check = HygieneCheck::create([
            'organization_id' => $request->user()->organization_id,
            'central_kitchen_id' => $request->user()->central_kitchen_id ?? CentralKitchen::first()->id,
            'check_type' => $data['check_type'],
            'area' => $data['area'],
            'items' => $items,
            'photo_path' => $request->hasFile('photo') ? $request->file('photo')->store('hygiene-photos', 'public') : null,
            'result' => $failed > 0 ? 'FAILED' : 'PASSED',
            'notes' => $data['notes'] ?? null,
            'checked_by' => $request->user()->id,
            'checked_at' => now(),
        ]);

        return redirect()->route('hygiene.index')->with($failed > 0 ? 'error' : 'success', "Checklist {$check->check_type}: {$check->result} ({$failed} temuan).");
    }
}
