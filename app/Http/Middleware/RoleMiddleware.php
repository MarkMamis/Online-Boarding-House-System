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
            return redirect()->route('login');
        }

        if (empty($roles)) {
            return $next($request);
        }

        // Allow comma-separated roles in a single parameter (e.g. role:admin,landlord)
        $allowedRoles = [];
        foreach ($roles as $role) {
            foreach (explode(',', $role) as $singleRole) {
                $singleRole = trim($singleRole);
                if ($singleRole !== '') {
                    $allowedRoles[] = $singleRole;
                }
            }
        }

        if (!in_array($user->role, $allowedRoles, true)) {
            abort(403);
        }

        return $next($request);
    }
}
