<?php

namespace App\Core\Traits;

trait BelongsToTenant
{
    protected static function bootBelongsToTenant(): void
    {
        static::creating(function ($model) {
            if (function_exists('tenant_id') && tenant_id()) {
                if (isset($model->tenant_id) && is_null($model->tenant_id)) {
                    $model->tenant_id = tenant_id();
                }
            }
        });
    }
}
