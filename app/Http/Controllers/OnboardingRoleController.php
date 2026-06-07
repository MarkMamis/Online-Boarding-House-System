<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class OnboardingRoleController extends Controller
{
    public function show()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $canChangeRole = in_array($user->role, ['student', 'landlord'], true)
            && !($user->onboarding_complete ?? false);

        if ($user->role !== 'pending' && !$canChangeRole) {
            return $this->redirectForResolvedRole($user);
        }

        $isRoleChange = $user->role !== 'pending';

        return view('onboarding.role', compact('user', 'isRoleChange'));
    }

    public function store(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $canChangeRole = in_array($user->role, ['student', 'landlord'], true)
            && !($user->onboarding_complete ?? false);

        if ($user->role !== 'pending' && !$canChangeRole) {
            return $this->redirectForResolvedRole($user);
        }

        $validator = Validator::make($request->all(), [
            'role' => 'required|in:student,landlord',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        $role = (string) $request->input('role');

        $user->forceFill([
            'role' => $role,
            'onboarding_complete' => false,
        ])->save();

        if ($role === 'landlord') {
            $user->landlordProfile()->firstOrCreate(
                ['user_id' => $user->id],
                [
                    'contact_number' => $user->contact_number,
                    'boarding_house_name' => $user->boarding_house_name,
                    'business_permit_status' => 'not_submitted',
                    'profile_completed' => false,
                    'billing_completed' => false,
                ]
            );

            return redirect()->route('landlord.setup.show');
        }

        return redirect()->route('student.setup.show');
    }

    private function redirectForResolvedRole(\App\Models\User $user)
    {
        if ($user->role === 'landlord') {
            return redirect()->route(($user->onboarding_complete ?? false) ? 'landlord.dashboard' : 'landlord.setup.show');
        }

        if ($user->role === 'student') {
            return redirect()->route(($user->onboarding_complete ?? false) ? 'student.dashboard' : 'student.setup.show');
        }

        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('onboarding.role.show');
    }
}
