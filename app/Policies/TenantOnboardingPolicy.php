<?php

namespace App\Policies;

use App\Models\TenantOnboarding;
use App\Models\User;

class TenantOnboardingPolicy
{
    public function before(User $user, string $ability): bool|null
    {
        return $user->role === 'admin' ? true : null;
    }

    private function isStudentOwner(User $user, TenantOnboarding $onboarding): bool
    {
        $onboarding->loadMissing('booking');
        return $user->role === 'student' && $onboarding->booking && (int) $onboarding->booking->student_id === (int) $user->id;
    }

    private function isLandlordOwner(User $user, TenantOnboarding $onboarding): bool
    {
        if ($user->role !== 'landlord') {
            return false;
        }
        $onboarding->loadMissing('booking.room.property');
        return $onboarding->booking
            && $onboarding->booking->room
            && $onboarding->booking->room->property
            && (int) $onboarding->booking->room->property->landlord_id === (int) $user->id;
    }

    public function view(User $user, TenantOnboarding $onboarding): bool
    {
        return $this->isStudentOwner($user, $onboarding) || $this->isLandlordOwner($user, $onboarding);
    }

    public function uploadDocuments(User $user, TenantOnboarding $onboarding): bool
    {
        return $this->isStudentOwner($user, $onboarding);
    }

    public function signContract(User $user, TenantOnboarding $onboarding): bool
    {
        return $this->isStudentOwner($user, $onboarding);
    }

    public function signContractAsLandlord(User $user, TenantOnboarding $onboarding): bool
    {
        return $this->isLandlordOwner($user, $onboarding);
    }

    public function payDeposit(User $user, TenantOnboarding $onboarding): bool
    {
        return $this->isStudentOwner($user, $onboarding);
    }

    public function reviewDocuments(User $user, TenantOnboarding $onboarding): bool
    {
        return $this->isLandlordOwner($user, $onboarding);
    }

    public function approveDocuments(User $user, TenantOnboarding $onboarding): bool
    {
        return $this->isLandlordOwner($user, $onboarding);
    }

    public function viewDocument(User $user, TenantOnboarding $onboarding): bool
    {
        return $this->view($user, $onboarding);
    }
}
