<?php

namespace App\Services\AI;

use App\Models\User;
use App\Services\AdvisoryResult;
use Illuminate\Support\Facades\DB;

class AiAuditLogger
{
    public function log(User $user, string $topic, AdvisoryResult $result, string $provider = 'deterministic'): void
    {
        try {
            DB::table('audit_logs')->insert([
                'organization_id' => $user->organization_id,
                'user_id' => $user->id,
                'action' => 'AI_ADVISORY',
                'model_type' => 'ai:'.$provider,
                'model_id' => 0,
                'old_data' => null,
                'new_data' => json_encode(['topic' => $topic] + $result->toArray()),
                'ip_address' => request()->ip(),
                'user_agent' => substr((string) request()->userAgent(), 0, 200),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable) {
        }
    }
}
