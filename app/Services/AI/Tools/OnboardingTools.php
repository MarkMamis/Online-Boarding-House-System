<?php

namespace App\Services\AI\Tools;

use App\Models\Booking;
use App\Models\Property;
use App\Models\TenantOnboarding;
use App\Models\User;

class OnboardingTools
{
    /**
     * Get aggregate onboarding statistics (Admin or Landlord).
     */
    public function getOnboardingStatistics(User $user, array $args): array
    {
        $query = TenantOnboarding::query();

        if ($user->role === 'landlord') {
            $propertyIds = Property::where('landlord_id', $user->id)->pluck('id');
            $query->whereHas('booking.room', function ($q) use ($propertyIds) {
                $q->whereIn('property_id', $propertyIds);
            });
        }

        $total = (clone $query)->count();
        $completed = (clone $query)->where('status', 'completed')->count();
        $pending = (clone $query)->where('status', 'pending')->count();
        $inProgress = (clone $query)->where('status', 'in_progress')->count();

        return [
            'total_onboardings' => $total,
            'completed' => $completed,
            'pending' => $pending,
            'in_progress' => $inProgress,
        ];
    }

    /**
     * Get tenant onboarding status for current student.
     */
    public function getMyOnboardingStatus(User $user, array $args): array
    {
        $latestApproved = Booking::with(['tenantOnboarding', 'room.property'])
            ->where('student_id', $user->id)
            ->where('status', 'approved')
            ->latest('id')
            ->first();

        if (!$latestApproved) {
            return [
                'has_onboarding' => false,
                'message' => 'No approved booking requiring tenant onboarding found.',
            ];
        }

        $onboarding = $latestApproved->tenantOnboarding;
        if (!$onboarding) {
            return [
                'has_onboarding' => true,
                'status' => 'not_started',
                'property_name' => $latestApproved->room?->property?->name ?? 'Unknown',
                'action_url' => '/student/onboarding',
            ];
        }

        return [
            'has_onboarding' => true,
            'status' => $onboarding->status,
            'property_name' => $latestApproved->room?->property?->name ?? 'Unknown',
            'room_number' => $latestApproved->room?->room_number ?? 'N/A',
            'contract_signed' => (bool) $onboarding->contract_signed,
            'landlord_contract_signed' => (bool) $onboarding->landlord_contract_signed,
            'deposit_paid' => (bool) $onboarding->deposit_paid,
            'deposit_amount' => (float) ($onboarding->deposit_amount ?: 0),
            'action_url' => '/student/onboarding',
        ];
    }
}
