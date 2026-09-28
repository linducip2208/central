<?php

namespace App\Services;

use App\Models\Approval;
use App\Models\ApprovalDelegation;
use App\Models\ApprovalMatrix;
use App\Models\User;
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

    /**
     * Matriks persetujuan: peran yang disyaratkan untuk nominal tertentu.
     * null = tidak ada matriks → cukup permission route.
     */
    public function requiredRole(int $orgId, string $type, float $amount): ?array
    {
        $row = ApprovalMatrix::where('organization_id', $orgId)
            ->where('approvable_type', $type)
            ->where('min_amount', '<=', $amount)
            ->orderByDesc('level')->first();
        if (! $row) {
            return null;
        }

        return ['role' => $row->role, 'level' => (int) $row->level];
    }

    /** Cek kelayakan pemutus: bypass admin, peran matriks, atau delegasi aktif. */
    public function canDecide(User $user, Model $model, float $amount = 0): bool
    {
        if ($user->hasRole(['super-admin', 'admin'])) {
            return true;
        }
        $req = $this->requiredRole((int) $user->organization_id, class_basename($model), $amount);
        if (! $req) {
            return true;
        }
        if ($user->hasRole($req['role'])) {
            return true;
        }
        // Delegasi: user adalah penerima delegasi aktif dari pemilik peran.
        $delegatorIds = User::role($req['role'])->pluck('id');
        foreach ($delegatorIds as $delegatorId) {
            $to = ApprovalDelegation::effective()
                ->where('organization_id', $user->organization_id)
                ->where('delegator_id', $delegatorId)
                ->where('delegate_id', $user->id)
                ->exists();
            if ($to) {
                return true;
            }
        }

        return false;
    }

    public function inbox(int $organizationId, int $limit = 20)
    {
        return Approval::where('organization_id', $organizationId)->with(['decider'])->latest()->take($limit)->get();
    }
}
