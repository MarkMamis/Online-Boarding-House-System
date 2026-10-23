<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureRoleSelected
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            abort(403);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->role === 'pending') {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Choose whether you are registering as a student or landlord before continuing.',
                    'redirect' => route('onboarding.role.show'),
                ], 403);
            }

            return redirect()
                ->route('onboarding.role.show')
                ->with('error', 'Choose whether you are registering as a student or landlord before continuing.');
        }

        if ($user->role === 'student' && (! ($user->onboarding_complete ?? false) || ! $user->isStudentSetupComplete())) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Complete your student verification setup to unlock the full student portal.',
                    'redirect' => route('student.setup.show'),
                ], 403);
            }

            return redirect()
                ->route('student.setup.show')
                ->with('error', 'Complete your student verification setup to unlock the full student portal.');
        }

        if ($user->role === 'landlord' && ! ($user->onboarding_complete ?? false)) {
            $user->loadMissing('landlordProfile');
            $landlordProfile = $user->landlordProfile;

            $profileComplete = filled($user->contact_number)
                && filled($user->boarding_house_name)
                && filled(optional($landlordProfile)->about);

            if ($profileComplete) {
                $user->forceFill(['onboarding_complete' => true])->save();

                return $next($request);
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Complete your landlord setup before accessing this feature.',
                    'redirect' => route('landlord.setup.show'),
                ], 403);
            }

            return redirect()
                ->route('landlord.setup.show')
                ->with('error', 'Complete your landlord setup before accessing the rest of the portal.');
        }

        return $next($request);
    }
}
