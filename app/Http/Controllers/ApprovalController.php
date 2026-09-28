<?php

namespace App\Http\Controllers;

use App\Models\Approval;
use App\Models\ApprovalDelegation;
use App\Models\ApprovalMatrix;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class ApprovalController extends Controller
{
    /** Kotak masuk keputusan persetujuan (jejak audit reusable engine). */
    public function inbox(Request $request)
    {
        $approvals = Approval::with(['decider', 'requester', 'approvable'])
            ->where('organization_id', $request->user()->organization_id)
            ->latest()->paginate(25);
        $approvals->getCollection()->transform(function ($a) {
            $ref = $a->approvable;
            $a->ref_label = $ref ? ($ref->number ?? $ref->code ?? $ref->name ?? '#'.$ref->getKey()) : '(terhapus)';

            return $a;
        });

        return view('approvals.inbox', compact('approvals'));
    }

    public function matrix(Request $request)
    {
        $matrices = ApprovalMatrix::where('organization_id', $request->user()->organization_id)->orderBy('approvable_type')->orderBy('level')->get();
        $roles = Role::orderBy('name')->get();

        return view('approvals.matrix', compact('matrices', 'roles'));
    }

    public function storeMatrix(Request $request)
    {
        $data = $request->validate([
            'approvable_type' => 'required|in:PurchaseRequest,PurchaseOrder,SupplierInvoice,Recall,Bom,Menu,StockOpname',
            'min_amount' => 'required|numeric|min:0',
            'level' => 'required|integer|min:1|max:5',
            'role' => 'required|exists:roles,name',
        ]);
        ApprovalMatrix::updateOrCreate(
            ['organization_id' => $request->user()->organization_id, 'approvable_type' => $data['approvable_type'], 'level' => $data['level']],
            $data
        );

        return back()->with('success', 'Matriks tersimpan.');
    }

    public function destroyMatrix(ApprovalMatrix $matrix)
    {
        abort_unless((int) $matrix->organization_id === (int) request()->user()->organization_id, 403);
        $matrix->delete();

        return back()->with('success', 'Matriks dihapus.');
    }

    public function delegations(Request $request)
    {
        $delegations = ApprovalDelegation::with(['delegator', 'delegate'])
            ->where('organization_id', $request->user()->organization_id)->latest()->take(30)->get();
        $users = User::where('organization_id', $request->user()->organization_id)->active()->orderBy('name')->get();

        return view('approvals.delegations', compact('delegations', 'users'));
    }

    public function storeDelegation(Request $request)
    {
        $data = $request->validate([
            'delegator_id' => 'required|exists:users,id|different:delegate_id',
            'delegate_id' => 'required|exists:users,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);
        foreach (['delegator_id', 'delegate_id'] as $k) {
            $u = User::findOrFail($data[$k]);
            abort_unless((int) $u->organization_id === (int) $request->user()->organization_id, 403);
        }
        ApprovalDelegation::create($data + ['organization_id' => $request->user()->organization_id, 'is_active' => true]);

        return back()->with('success', 'Delegasi dibuat.');
    }

    public function toggleDelegation(ApprovalDelegation $delegation)
    {
        abort_unless((int) $delegation->organization_id === (int) request()->user()->organization_id, 403);
        $delegation->update(['is_active' => ! $delegation->is_active]);

        return back()->with('success', 'Delegasi diperbarui.');
    }
}
