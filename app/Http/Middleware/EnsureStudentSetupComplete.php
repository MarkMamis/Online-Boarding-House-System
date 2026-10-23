<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureStudentSetupComplete
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            abort(403);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->role !== 'student') {
            return $next($request);
        }

        if (! $user->isStudentSetupComplete()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Please complete your student verification setup to access this feature.',
                    'redirect' => route('student.setup.show'),
                ], 403);
            }

            return redirect()
                ->route('student.setup.show')
                ->with('error', 'Complete your student verification setup to unlock the full student portal.');
        }

        return $next($request);
    }
}
