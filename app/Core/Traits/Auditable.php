<?php

namespace App\Core\Traits;

use Illuminate\Support\Facades\Auth;

trait Auditable
{
    protected static function bootAuditable(): void
    {
        static::created(function ($model) {
            $model->logActivity('CREATE', 'created');
        });

        static::updated(function ($model) {
            $model->logActivity('UPDATE', 'updated');
        });

        static::deleted(function ($model) {
            $model->logActivity('DELETE', 'deleted');
        });
    }

    protected function logActivity(string $action, string $description): void
    {
        try {
            activity()
                ->causedBy(Auth::user())
                ->performedOn($this)
                ->withProperties([
                    'old' => $this->getOriginal() ?? [],
                    'new' => $this->getAttributes(),
                ])
                ->useLog('audit_log')
                ->log($action.' '.$description);
        } catch (\Throwable) {
            // Audit tidak boleh menggagalkan transaksi bisnis.
        }
    }
}
