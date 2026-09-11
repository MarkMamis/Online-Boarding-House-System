<?php

namespace App\Services\AI\Tools;

use App\Models\Booking;
use App\Models\LandlordProfile;
use App\Models\Property;
use App\Models\Report;
use App\Models\Room;
use App\Models\User;
use App\Services\BoardingMonitoringService;

class DashboardTools
{
    protected BoardingMonitoringService $boardingMonitoringService;

    public function __construct(BoardingMonitoringService $boardingMonitoringService)
    {
        $this->boardingMonitoringService = $boardingMonitoringService;
    }

    /**
     * Get role-tailored dashboard statistics.
     */
    public function getDashboardStatistics(User $user, array $args): array
    {
        if ($user->role === 'admin') {
            return $this->getAdminDashboard();
        }

        if ($user->role === 'landlord') {
            return $this->getLandlordDashboard($user);
        }

        return $this->getStudentDashboard($user);
    }

    /**
     * Get complete system summary for high-level status inquiries (Admin or Landlord).
     */
    public function getSystemSummary(User $user, array $args): array
    {
        if ($user->role === 'admin') {
            $totalStudents = User::where('role', 'student')->count();
            $totalLandlords = User::where('role', 'landlord')->count();
            $totalProperties = Property::count();
            $approvedProperties = Property::where('approval_status', 'approved')->count();
            $pendingPropertyApprovals = Property::where('approval_status', 'pending')->count();
            $pendingReports = Report::whereIn('status', ['pending', 'in_progress'])->count();
            $pendingStudentVerifications = User::where('role', 'student')
                ->where('school_id_verification_status', 'pending')
                ->count();

            $today = now()->toDateString();
            $activeBoarded = Booking::where('status', 'approved')
                ->whereDate('check_in', '<=', $today)
                ->whereDate('check_out', '>', $today)
                ->distinct('student_id')
                ->count('student_id');

            return [
                'as_of_date' => now()->toFormattedDateString(),
                'total_registered_users' => User::count(),
                'students_count' => $totalStudents,
                'landlords_count' => $totalLandlords,
                'active_boarded_students' => $activeBoarded,
                'total_properties' => $totalProperties,
                'approved_properties' => $approvedProperties,
                'pending_property_approvals' => $pendingPropertyApprovals,
                'pending_student_verifications' => $pendingStudentVerifications,
                'unresolved_reports' => $pendingReports,
            ];
        }

        if ($user->role === 'landlord') {
            return $this->getLandlordDashboard($user);
        }

        return $this->getStudentDashboard($user);
    }

    /**
     * Get deep boarding monitoring metrics using BoardingMonitoringService (Admin only).
     */
    public function getBoardingStatistics(User $user, array $args): array
    {
        $baseQuery = Booking::query();
        $metrics = $this->boardingMonitoringService->getSummaryMetrics($baseQuery);

        return [
            'as_of_date' => now()->toFormattedDateString(),
            'total_booking_records' => $metrics['total_records'] ?? 0,
            'unique_students' => $metrics['unique_students'] ?? 0,
            'active_boardings' => $metrics['active_boardings'] ?? 0,
            'active_tenants' => $metrics['active_tenants'] ?? 0,
            'checked_out_tenants' => $metrics['checked_out_tenants'] ?? 0,
            'pending_boardings' => $metrics['pending_boardings'] ?? 0,
            'active_rooms' => $metrics['active_rooms'] ?? 0,
            'active_properties' => $metrics['active_properties'] ?? 0,
        ];
    }

    private function getAdminDashboard(): array
    {
        $totalStudents = User::where('role', 'student')->count();
        $totalLandlords = User::where('role', 'landlord')->count();
        $totalProperties = Property::count();
        $pendingApprovals = Property::where('approval_status', 'pending')->count();
        $pendingReports = Report::where('status', 'pending')->count();
        $pendingVerifications = User::where('role', 'student')
            ->where('school_id_verification_status', 'pending')
            ->count();

        $today = now()->toDateString();
        $activeTenants = Booking::where('status', 'approved')
            ->whereDate('check_in', '<=', $today)
            ->whereDate('check_out', '>', $today)
            ->distinct('student_id')
            ->count('student_id');

        return [
            'role' => 'admin',
            'total_students' => $totalStudents,
            'total_landlords' => $totalLandlords,
            'total_properties' => $totalProperties,
            'pending_property_approvals' => $pendingApprovals,
            'pending_student_verifications' => $pendingVerifications,
            'pending_reports' => $pendingReports,
            'active_tenants' => $activeTenants,
        ];
    }

    private function getLandlordDashboard(User $user): array
    {
        $propertyIds = Property::where('landlord_id', $user->id)->pluck('id');
        $rooms = Room::whereIn('property_id', $propertyIds)->get();
        $roomIds = $rooms->pluck('id');

        $today = now()->toDateString();
        $activeTenants = Booking::whereIn('room_id', $roomIds)
            ->where('status', 'approved')
            ->whereDate('check_in', '<=', $today)
            ->whereDate('check_out', '>', $today)
            ->distinct('student_id')
            ->count('student_id');

        $pendingBookings = Booking::whereIn('room_id', $roomIds)
            ->where('status', 'pending')
            ->count();

        $occupiedSlots = 0;
        $totalCapacity = (int) $rooms->sum('capacity');
        foreach ($rooms as $room) {
            $snap = $room->occupancySnapshot();
            $occupiedSlots += (int) ($snap['occupied_slots'] ?? 0);
        }

        $rate = $totalCapacity > 0 ? round(($occupiedSlots / $totalCapacity) * 100, 1) : 0;

        return [
            'role' => 'landlord',
            'properties_count' => count($propertyIds),
            'rooms_count' => count($rooms),
            'active_tenants' => $activeTenants,
            'pending_bookings' => $pendingBookings,
            'total_bed_capacity' => $totalCapacity,
            'occupied_slots' => $occupiedSlots,
            'occupancy_rate_percent' => $rate,
        ];
    }

    private function getStudentDashboard(User $user): array
    {
        $today = now()->toDateString();
        $activeBooking = Booking::with('room.property')
            ->where('student_id', $user->id)
            ->where('status', 'approved')
            ->whereDate('check_in', '<=', $today)
            ->whereDate('check_out', '>', $today)
            ->first();

        $pendingRequests = Booking::where('student_id', $user->id)
            ->where('status', 'pending')
            ->count();

        return [
            'role' => 'student',
            'is_currently_boarded' => $activeBooking !== null,
            'boarded_property' => $activeBooking?->room?->property?->name,
            'boarded_room' => $activeBooking?->room?->room_number,
            'pending_booking_requests' => $pendingRequests,
            'profile_setup_complete' => $user->isStudentSetupComplete(),
            'school_id_verification' => $user->school_id_verification_status ?: 'unverified',
        ];
    }
}
