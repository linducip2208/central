<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();
        if (! $user) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return redirect()->route('login');
        }
        foreach ($permissions as $permission) {
            if (method_exists($user, 'can') && $user->can($permission)) {
                return $next($request);
            }
            if (method_exists($user, 'hasRole') && $user->hasRole(['super-admin', 'admin'])) {
                return $next($request);
            }
        }
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }
        abort(403, 'Tidak memiliki akses.');
    }
}
