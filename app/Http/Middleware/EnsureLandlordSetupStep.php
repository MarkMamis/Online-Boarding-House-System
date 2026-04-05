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

        $permitSubmitted = filled(optional($landlordProfile)->business_permit_path);
        $permitStatus = (string) (optional($landlordProfile)->business_permit_status ?: ($permitSubmitted ? 'pending' : 'not_submitted'));
        $permitApproved = $permitSubmitted && $permitStatus === 'approved';

        if ($step === 'profile_complete' && !$profileComplete) {
            return $this->redirectToSetup(
                'Complete your profile first before using landlord operations.',
                'profile'
            );
        }

        if ($step === 'permit_submitted' && !$permitSubmitted) {
            return $this->redirectToSetup(
                'Upload your business permit first before setting up properties and rooms.',
                'permit'
            );
        }

        if ($step === 'permit_approved' && !$permitApproved) {
            $message = $permitStatus === 'rejected'
                ? 'Your business permit was rejected. Please upload a new permit and wait for admin approval.'
                : 'Your business permit is still pending admin approval. Landlord operations unlock after approval.';

            return $this->redirectToSetup($message, 'permit');
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
