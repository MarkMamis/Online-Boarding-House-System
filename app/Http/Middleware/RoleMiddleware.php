<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Usage: ->middleware('role:admin') or ->middleware('role:admin,landlord')
     */
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        $user = Auth::user();

        if (!$user) {
            if ($request->expectsJson()) {
                abort(401, 'Unauthenticated.');
            }

            return response()->view('errors.403', [], 403);
        }

        if (empty($roles)) {
            return $next($request);
        }

        // Allow comma-separated roles in a single parameter (e.g. role:admin,landlord)
        $allowedRoles = [];
        foreach ($roles as $role) {
            foreach (explode(',', $role) as $singleRole) {
                $singleRole = strtolower(trim($singleRole));
                if ($singleRole !== '') {
                    $allowedRoles[] = $singleRole;
                }
            }
        }

        $allowedRoles = array_values(array_unique($allowedRoles));
        $currentRole = strtolower((string) $user->role);

        if (!in_array($currentRole, $allowedRoles, true)) {
            if ($request->expectsJson()) {
                abort(403, 'Forbidden.');
            }

            return response()->view('errors.unauthorized', [
                'requiredRoles' => $allowedRoles,
                'currentRole' => $currentRole,
            ], 403);
        }

        return $next($request);
    }
}
