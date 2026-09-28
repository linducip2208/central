<?php

namespace App\Services;

use App\Models\Approval;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Reusable approval engine (append-only decision log).
 * Alur entity tetap memakai kolom status-nya; setiap keputusan
 * dicatat di sini agar ada jejak who/when/comment/level.
 */
class ApprovalService
{
    /**
     * @param  Model  $model  entity yang diputus
     * @param  string  $action  APPROVE|REJECT
     */
    public function decide(Model $model, string $action, ?string $comment = null, int $level = 1, ?int $decidedBy = null): Approval
    {
        return DB::transaction(function () use ($model, $action, $comment, $level, $decidedBy) {
            return Approval::create([
                'organization_id' => $model->organization_id ?? Auth::user()?->organization_id,
                'approvable_type' => $model::class,
                'approvable_id' => $model->getKey(),
                'action' => strtoupper($action),
                'level' => $level,
                'status' => strtoupper($action) === 'APPROVE' ? 'APPROVED' : 'REJECTED',
                'comment' => $comment,
                'requested_by' => $model->created_by ?? $model->requested_by ?? null,
                'decided_by' => $decidedBy ?? Auth::id(),
                'decided_at' => now(),
            ]);
        });
    }

    public function history(Model $model)
    {
        return Approval::where('approvable_type', $model::class)
            ->where('approvable_id', $model->getKey())
            ->with(['decider', 'requester'])
            ->latest()->get();
    }

    public function inbox(int $organizationId, int $limit = 20)
    {
        return Approval::where('organization_id', $organizationId)->with(['decider'])->latest()->take($limit)->get();
    }
}
