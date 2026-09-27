<?php

if (! function_exists('setting')) {
    function setting(string $key, $default = null)
    {
        return app('settings')->get($key, $default);
    }
}

if (! function_exists('tenant')) {
    function tenant()
    {
        return app('tenant');
    }
}

if (! function_exists('tenant_id')) {
    function tenant_id(): ?int
    {
        return tenant()?->id;
    }
}

if (! function_exists('user_can')) {
    function user_can(string $permission): bool
    {
        return Auth::user()?->hasPermissionTo($permission) ?? false;
    }
}

if (! function_exists('mbg_currency')) {
    function mbg_currency(float $amount, string $currency = 'IDR'): string
    {
        return match ($currency) {
            'IDR' => 'Rp '.number_format($amount, 0, ',', '.'),
            default => number_format($amount, 2),
        };
    }
}

if (! function_exists('mbg_quantity')) {
    function mbg_quantity(float $quantity, string $unit = 'kg'): string
    {
        return number_format($quantity, 2).' '.$unit;
    }
}

if (! function_exists('mbg_status_badge')) {
    function mbg_status_badge(string $status): string
    {
        $classes = [
            'DRAFT' => 'secondary',
            'SUBMITTED' => 'info',
            'APPROVED' => 'success',
            'REJECTED' => 'danger',
            'PENDING' => 'warning',
            'ACTIVE' => 'success',
            'INACTIVE' => 'secondary',
            'COMPLETED' => 'success',
            'FAILED' => 'danger',
            'EXPIRED' => 'danger',
            'AVAILABLE' => 'success',
            'BLOCKED' => 'warning',
        ];
        $class = $classes[$status] ?? 'secondary';

        return "<span class='badge bg-{$class}'>{$status}</span>";
    }
}
