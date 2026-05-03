<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureLandlordSetupStep
{
    public function handle(Request $request, Closure $next, string $step = 'profile_complete'): Response
    {
        if (!Auth::check() || Auth::user()->role !== 'landlord') {
            abort(403);
        }

        $user = Auth::user()->loadMissing('landlordProfile');
        $landlordProfile = $user->landlordProfile;

        $profileComplete = filled($user->contact_number)
            && filled($user->boarding_house_name)
            && filled(optional($landlordProfile)->about);

        if ($step === 'profile_complete' && !$profileComplete) {
            return $this->redirectToSetup(
                'Complete your profile first before using landlord operations.',
                'profile'
            );
        }

        return $next($request);
    }

    private function redirectToSetup(string $message, string $step): Response
    {
        return redirect()
            ->route('landlord.setup.show', ['step' => $step])
            ->with('error', $message);
    }
}
