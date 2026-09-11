<?php

namespace App\Services\AI\Tools;

use App\Models\Booking;
use App\Models\Property;
use App\Models\User;

class BookingTools
{
    /**
     * Count bookings by status.
     */
    public function countBookings(User $user, array $args): array
    {
        $status = strtolower(trim((string) ($args['status'] ?? 'all')));
        $query = Booking::query();

        if ($user->role === 'student') {
            $query->where('student_id', $user->id);
        } elseif ($user->role === 'landlord') {
            $propertyIds = Property::where('landlord_id', $user->id)->pluck('id');
            $query->whereHas('room', function ($q) use ($propertyIds) {
                $q->whereIn('property_id', $propertyIds);
            });
        }

        if ($status !== '' && $status !== 'all') {
            $query->where('status', $status);
        }

        $total = (clone $query)->count();
        $pending = (clone $query)->where('status', 'pending')->count();
        $approved = (clone $query)->where('status', 'approved')->count();
        $cancelled = (clone $query)->whereIn('status', ['cancelled', 'rejected'])->count();

        return [
            'total_bookings' => $total,
            'pending_count' => $pending,
            'approved_count' => $approved,
            'cancelled_rejected_count' => $cancelled,
            'status_filter' => $status,
        ];
    }

    /**
     * Get aggregate booking statistics.
     */
    public function getBookingStatistics(User $user, array $args): array
    {
        $query = Booking::query();

        if ($user->role === 'landlord') {
            $propertyIds = Property::where('landlord_id', $user->id)->pluck('id');
            $query->whereHas('room', function ($q) use ($propertyIds) {
                $q->whereIn('property_id', $propertyIds);
            });
        }

        $total = (clone $query)->count();
        $pending = (clone $query)->where('status', 'pending')->count();
        $approved = (clone $query)->where('status', 'approved')->count();
        $cancelled = (clone $query)->where('status', 'cancelled')->count();
        $rejected = (clone $query)->where('status', 'rejected')->count();

        $today = now()->toDateString();
        $activeStays = (clone $query)->where('status', 'approved')
            ->whereDate('check_in', '<=', $today)
            ->whereDate('check_out', '>', $today)
            ->count();

        return [
            'total_bookings' => $total,
            'pending_requests' => $pending,
            'approved_bookings' => $approved,
            'active_current_stays' => $activeStays,
            'cancelled_bookings' => $cancelled,
            'rejected_bookings' => $rejected,
        ];
    }

    /**
     * Get the authenticated student's active or latest booking.
     */
    public function getMyBooking(User $user, array $args): array
    {
        $today = now()->toDateString();

        // 1. Check for active stay
        $booking = Booking::with(['room.property.landlord'])
            ->where('student_id', $user->id)
            ->where('status', 'approved')
            ->whereDate('check_in', '<=', $today)
            ->whereDate('check_out', '>', $today)
            ->first();

        // 2. Or upcoming approved stay
        if (!$booking) {
            $booking = Booking::with(['room.property.landlord'])
                ->where('student_id', $user->id)
                ->where('status', 'approved')
                ->whereDate('check_in', '>', $today)
                ->first();
        }

        // 3. Or most recent booking request
        if (!$booking) {
            $booking = Booking::with(['room.property.landlord'])
                ->where('student_id', $user->id)
                ->latest('id')
                ->first();
        }

        if (!$booking) {
            return [
                'has_booking' => false,
                'message' => 'You currently have no bookings or requests.',
                'action_url' => '/student/rooms',
            ];
        }

        return [
            'has_booking' => true,
            'booking_id' => $booking->id,
            'status' => $booking->status,
            'property_name' => $booking->room?->property?->name ?? 'Unknown',
            'property_address' => $booking->room?->property?->address ?? 'N/A',
            'room_number' => $booking->room?->room_number ?? 'N/A',
            'landlord_name' => $booking->room?->property?->landlord?->name ?? 'N/A',
            'occupancy_mode' => $booking->occupancy_mode ?? 'solo',
            'monthly_rent' => (float) ($booking->monthly_rent_amount ?: $booking->room?->price ?: 0),
            'check_in' => $booking->check_in?->toFormattedDateString(),
            'check_out' => $booking->check_out?->toFormattedDateString(),
            'payment_status' => $booking->derivedPaymentStatus(),
            'action_url' => '/student/requests',
        ];
    }

    /**
     * Search bookings with filters.
     */
    public function searchBookings(User $user, array $args): array
    {
        $status = strtolower(trim((string) ($args['status'] ?? 'all')));
        $search = trim((string) ($args['search'] ?? ''));
        $limit = min(max((int) ($args['limit'] ?? 5), 1), 15);

        $query = Booking::with(['student', 'room.property']);

        if ($user->role === 'landlord') {
            $propertyIds = Property::where('landlord_id', $user->id)->pluck('id');
            $query->whereHas('room', function ($q) use ($propertyIds) {
                $q->whereIn('property_id', $propertyIds);
            });
        }

        if ($status !== '' && $status !== 'all') {
            $query->where('status', $status);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->whereHas('student', function ($sq) use ($search) {
                    $sq->where('name', 'like', "%{$search}%")
                       ->orWhere('full_name', 'like', "%{$search}%");
                })->orWhereHas('room.property', function ($pq) use ($search) {
                    $pq->where('name', 'like', "%{$search}%");
                });
            });
        }

        $results = $query->latest('id')
            ->take($limit)
            ->get()
            ->map(fn ($b) => [
                'booking_id' => $b->id,
                'status' => $b->status,
                'student_name' => $b->student?->full_name ?: $b->student?->name ?: 'Unknown',
                'property_name' => $b->room?->property?->name ?? 'Unknown',
                'room_number' => $b->room?->room_number ?? 'N/A',
                'check_in' => $b->check_in?->toFormattedDateString(),
                'check_out' => $b->check_out?->toFormattedDateString(),
            ])
            ->all();

        return [
            'total_matches' => count($results),
            'bookings' => $results,
        ];
    }
}
