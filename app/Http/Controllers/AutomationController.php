<?php

namespace App\Http\Controllers;

use App\Models\AutomationRule;
use Illuminate\Http\Request;

class AutomationController extends Controller
{
    public function index(Request $request)
    {
        $rules = AutomationRule::where('organization_id', $request->user()->organization_id)->get();

        return view('automation.index', compact('rules'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'event' => 'required|in:'.implode(',', AutomationRule::EVENTS),
            'action' => 'required|in:notify_role,webhook',
            'target_role' => 'nullable|exists:roles,name',
            'message' => 'nullable|string|max:255',
            'min_value' => 'nullable|numeric|min:0',
        ]);
        $conditions = [];
        if ($request->filled('min_value')) {
            $conditions['days'] = ['max' => (float) $request->min_value];
        }
        AutomationRule::create([
            'organization_id' => $request->user()->organization_id,
            'name' => $data['name'], 'event' => $data['event'],
            'conditions' => $conditions ?: null,
            'action' => $data['action'], 'target_role' => $data['target_role'] ?? null,
            'message' => $data['message'] ?? null, 'is_active' => true,
        ]);

        return back()->with('success', 'Rule otomatisasi dibuat.');
    }

    public function toggle(AutomationRule $rule)
    {
        abort_unless((int) $rule->organization_id === (int) request()->user()->organization_id, 403);
        $rule->update(['is_active' => ! $rule->is_active]);

        return back()->with('success', 'Rule diperbarui.');
    }

    public function destroy(AutomationRule $rule)
    {
        abort_unless((int) $rule->organization_id === (int) request()->user()->organization_id, 403);
        $rule->delete();

        return back()->with('success', 'Rule dihapus.');
    }
}
