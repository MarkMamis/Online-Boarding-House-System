<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class EnsureAccountIsActive
{
    /**
     * Handle an incoming request and ensure active account status.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if ($user && Schema::hasColumn('users', 'is_active') && $user->is_active === false) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                abort(403, 'Your account is deactivated. Please contact the administrator.');
            }

            return redirect()->route('login')
                ->withErrors(['email' => 'Your account is deactivated. Please contact the administrator.']);
        }

        return $next($request);
    }
}
