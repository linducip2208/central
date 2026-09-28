<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\DB;

class RecordAuthAudit
{
    public function handle(Login|Logout $event): void
    {
        try {
            DB::table('audit_logs')->insert([
                'organization_id' => $event->user->organization_id ?? null,
                'user_id' => $event->user->getKey(),
                'action' => $event instanceof Login ? 'LOGIN' : 'LOGOUT',
                'model_type' => $event->user::class,
                'model_id' => $event->user->getKey(),
                'ip_address' => request()->ip(),
                'user_agent' => substr((string) request()->userAgent(), 0, 500),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable) {
            // Audit tidak boleh menggagalkan login/logout.
        }
    }
}
