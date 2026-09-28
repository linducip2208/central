<?php

namespace App\Http\Controllers;

use App\Models\Approval;
use Illuminate\Http\Request;

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
}
