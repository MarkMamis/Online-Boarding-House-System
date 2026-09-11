<?php

namespace App\Services\AI\Tools;

use App\Models\Booking;
use App\Models\Property;
use App\Models\Room;
use App\Models\User;
use App\Services\LandlordDocumentStatusService;

class LandlordTools
{
    /**
     * Count landlords registered in OBHS (Admin only).
     */
    public function countLandlords(User $user, array $args): array
    {
        $status = strtolower(trim((string) ($args['status'] ?? 'all')));

        $query = User::where('role', 'landlord');
        if ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        }

        $total = (clone $query)->count();

        $service = app(LandlordDocumentStatusService::class);
        $docStats = $service->getAggregateStatistics();

        return [
            'total_landlords' => $total,
            'approved_permits' => $docStats['business_permit']['approved'],
            'missing_permits' => $docStats['business_permit']['missing'],
            'pending_permits' => $docStats['business_permit']['pending'],
            'safety_certificates_missing' => $docStats['safety_certificate']['missing'],
            'landlords_with_missing_documents' => $docStats['landlords_with_missing_documents'],
            'fully_compliant_landlords' => $docStats['fully_document_complete_landlords'],
            'status_filter' => $status,
            'action_url' => '/admin/approvals/landlords?tab=permits',
            'action_label' => 'Review Landlord Documents',
        ];
    }

    /**
     * Get aggregate statistics on landlords and their listings (Admin only).
     */
    public function getLandlordStatistics(User $user, array $args): array
    {
        $totalLandlords = User::where('role', 'landlord')->count();
        $activeLandlords = User::where('role', 'landlord')->where('is_active', true)->count();
        $landlordsWithProperties = Property::distinct('landlord_id')->count('landlord_id');

        $service = app(LandlordDocumentStatusService::class);
        $docStats = $service->getAggregateStatistics();

        return [
            'total_landlords' => $totalLandlords,
            'active_landlords' => $activeLandlords,
            'landlords_with_properties' => $landlordsWithProperties,
            'business_permits' => $docStats['business_permit'],
            'safety_certificates' => $docStats['safety_certificate'],
            'landlords_with_missing_documents' => $docStats['landlords_with_missing_documents'],
            'total_missing_document_instances' => $docStats['total_missing_document_instances'],
            'fully_compliant_landlords' => $docStats['fully_document_complete_landlords'],
            'action_url' => '/admin/approvals/landlords?tab=permits',
            'action_label' => 'Review Landlord Documents',
        ];
    }

    /**
     * Get compliance and document status for a specific landlord (Landlord self, or Admin).
     */
    public function getLandlordDocumentStatus(User $user, array $args): array
    {
        $targetLandlord = $user;

        if ($user->role === 'admin' && !empty($args['landlord_id']) && is_numeric($args['landlord_id'])) {
            $found = User::where('id', (int) $args['landlord_id'])->where('role', 'landlord')->first();
            if ($found) {
                $targetLandlord = $found;
            } else {
                return [
                    'error' => 'Landlord not found with specified ID.',
                ];
            }
        } elseif ($user->role === 'admin' && !empty($args['query'])) {
            $queryStr = trim((string) $args['query']);
            $found = User::where('role', 'landlord')
                ->where(function ($q) use ($queryStr) {
                    $q->where('full_name', 'like', "%{$queryStr}%")
                      ->orWhere('email', 'like', "%{$queryStr}%");
                })->first();

            if ($found) {
                $targetLandlord = $found;
            }
        }

        if ($targetLandlord->role !== 'landlord') {
            return [
                'error' => 'Specified user is not a registered landlord.',
            ];
        }

        $service = app(LandlordDocumentStatusService::class);
        $summary = $service->getLandlordDocumentSummary($targetLandlord);

        return [
            'landlord_id' => $targetLandlord->id,
            'name' => $targetLandlord->full_name,
            'business_permit_status' => $summary['business_permit_status'],
            'safety_certificate_status' => $summary['safety_certificate_status'],
            'missing_documents' => $summary['missing_documents'],
            'pending_documents' => $summary['pending_documents'],
            'approved_documents' => $summary['approved_documents'],
            'rejected_documents' => $summary['rejected_documents'],
            'is_fully_compliant' => $summary['is_fully_compliant'],
            'action_url' => $user->role === 'admin' ? '/admin/approvals/landlords?tab=permits' : '/landlord/setup',
            'action_label' => $user->role === 'admin' ? 'Review Landlord Documents' : 'Landlord Setup',
        ];
    }

    /**
     * List properties owned by the current landlord (or queried landlord for admin).
     */
    public function getLandlordProperties(User $user, array $args): array
    {
        $landlordId = $user->id;
        if ($user->role === 'admin' && !empty($args['landlord_id']) && is_numeric($args['landlord_id'])) {
            $landlordId = (int) $args['landlord_id'];
        }

        $properties = Property::withCount('rooms')
            ->where('landlord_id', $landlordId)
            ->get();

        if ($properties->isEmpty()) {
            return [
                'total_properties' => 0,
                'properties' => [],
                'message' => 'No properties found.',
            ];
        }

        $list = $properties->map(fn ($p) => [
            'id' => $p->id,
            'name' => $p->name,
            'address' => $p->address,
            'approval_status' => $p->approval_status,
            'rooms_count' => $p->rooms_count,
            'price_min' => (float) ($p->price_min ?: 0),
            'price_max' => (float) ($p->price_max ?: 0),
            'rating' => (float) ($p->average_rating ?: 0),
        ])->all();

        return [
            'total_properties' => count($list),
            'properties' => $list,
        ];
    }

    /**
     * Get tenant and room statistics for the current landlord.
     */
    public function getLandlordTenantStatistics(User $user, array $args): array
    {
        $landlordId = $user->id;
        if ($user->role === 'admin' && !empty($args['landlord_id']) && is_numeric($args['landlord_id'])) {
            $landlordId = (int) $args['landlord_id'];
        }

        $propertyIds = Property::where('landlord_id', $landlordId)->pluck('id');
        $rooms = Room::whereIn('property_id', $propertyIds)->get();
        $roomIds = $rooms->pluck('id');

        $totalCapacity = (int) $rooms->sum('capacity');

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
        $availableSlots = 0;
        foreach ($rooms as $room) {
            $snap = $room->occupancySnapshot();
            $occupiedSlots += (int) ($snap['occupied_slots'] ?? 0);
            $availableSlots += (int) ($snap['available_slots'] ?? 0);
        }

        $occupancyRate = $totalCapacity > 0 ? round(($occupiedSlots / $totalCapacity) * 100, 1) : 0;

        return [
            'total_properties' => count($propertyIds),
            'total_rooms' => count($rooms),
            'total_bed_capacity' => $totalCapacity,
            'occupied_slots' => $occupiedSlots,
            'available_slots' => $availableSlots,
            'active_tenants' => $activeTenants,
            'pending_booking_requests' => $pendingBookings,
            'overall_occupancy_rate_percent' => $occupancyRate,
        ];
    }
}
