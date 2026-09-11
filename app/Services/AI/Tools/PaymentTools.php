<?php

namespace App\Services\AI\Tools;

use App\Models\Booking;
use App\Models\Property;
use App\Models\TenantPayment;
use App\Models\User;

class PaymentTools
{
    /**
     * Get payment status and dues for the authenticated student.
     */
    public function getMyPaymentStatus(User $user, array $args): array
    {
        $today = now()->toDateString();
        $booking = Booking::with(['room.property', 'tenantPayments'])
            ->where('student_id', $user->id)
            ->where('status', 'approved')
            ->whereDate('check_in', '<=', $today)
            ->whereDate('check_out', '>', $today)
            ->first();

        if (!$booking) {
            return [
                'has_active_stay' => false,
                'message' => 'You do not have an active room stay with rental payments.',
            ];
        }

        $isOverdue = $booking->isPaymentOverdue();
        $derivedStatus = $booking->derivedPaymentStatus();
        $dueDate = $booking->resolvePaymentDueDate();

        $recentPayments = $booking->tenantPayments()
            ->latest('submitted_at')
            ->take(3)
            ->get()
            ->map(fn ($p) => [
                'amount' => (float) $p->amount_due,
                'status' => $p->status,
                'due_date' => $p->due_date?->toFormattedDateString(),
                'submitted_at' => $p->submitted_at?->toFormattedDateString(),
            ])
            ->all();

        return [
            'has_active_stay' => true,
            'booking_id' => $booking->id,
            'property_name' => $booking->room?->property?->name ?? 'Unknown',
            'room_number' => $booking->room?->room_number ?? 'N/A',
            'monthly_rent' => (float) ($booking->monthly_rent_amount ?: $booking->room?->price ?: 0),
            'payment_status' => $derivedStatus,
            'is_overdue' => $isOverdue,
            'next_payment_due_date' => $dueDate?->toFormattedDateString() ?? 'Not specified',
            'recent_payments' => $recentPayments,
            'action_url' => '/student/payments',
        ];
    }

    /**
     * Get aggregate payment statistics (Landlord or Admin).
     */
    public function getPaymentStatistics(User $user, array $args): array
    {
        $paymentQuery = TenantPayment::query();
        $bookingQuery = Booking::where('status', 'approved');

        if ($user->role === 'landlord') {
            $propertyIds = Property::where('landlord_id', $user->id)->pluck('id');
            $paymentQuery->whereHas('booking.room', function ($q) use ($propertyIds) {
                $q->whereIn('property_id', $propertyIds);
            });
            $bookingQuery->whereHas('room', function ($q) use ($propertyIds) {
                $q->whereIn('property_id', $propertyIds);
            });
        }

        $totalSubmitted = (clone $paymentQuery)->count();
        $pendingVerification = (clone $paymentQuery)->where('status', 'submitted')->count();
        $approvedPayments = (clone $paymentQuery)->where('status', 'approved')->count();
        $rejectedPayments = (clone $paymentQuery)->where('status', 'rejected')->count();

        // Count overdue active bookings
        $today = now()->toDateString();
        $activeBookings = (clone $bookingQuery)
            ->whereDate('check_in', '<=', $today)
            ->whereDate('check_out', '>', $today)
            ->get();

        $overdueCount = 0;
        foreach ($activeBookings as $b) {
            if ($b->isPaymentOverdue()) {
                $overdueCount++;
            }
        }

        return [
            'total_submitted_payments' => $totalSubmitted,
            'pending_review' => $pendingVerification,
            'approved_payments' => $approvedPayments,
            'rejected_payments' => $rejectedPayments,
            'active_tenancies' => $activeBookings->count(),
            'overdue_tenancies' => $overdueCount,
        ];
    }

    /**
     * Get overdue payment statistics (Landlord or Admin).
     */
    public function getOverduePaymentStatistics(User $user, array $args): array
    {
        $bookingQuery = Booking::with(['student', 'room.property'])
            ->where('status', 'approved');

        if ($user->role === 'landlord') {
            $propertyIds = Property::where('landlord_id', $user->id)->pluck('id');
            $bookingQuery->whereHas('room', function ($q) use ($propertyIds) {
                $q->whereIn('property_id', $propertyIds);
            });
        }

        $today = now()->toDateString();
        $activeBookings = $bookingQuery
            ->whereDate('check_in', '<=', $today)
            ->whereDate('check_out', '>', $today)
            ->get();

        $overdueList = [];
        foreach ($activeBookings as $b) {
            if ($b->isPaymentOverdue()) {
                $dueDate = $b->resolvePaymentDueDate();
                $overdueList[] = [
                    'booking_id' => $b->id,
                    'student_name' => $b->student?->full_name ?: $b->student?->name ?: 'Unknown',
                    'property_name' => $b->room?->property?->name ?? 'Unknown',
                    'room_number' => $b->room?->room_number ?? 'N/A',
                    'monthly_rent' => (float) ($b->monthly_rent_amount ?: $b->room?->price ?: 0),
                    'due_date' => $dueDate?->toFormattedDateString(),
                ];
            }
        }

        return [
            'total_overdue_count' => count($overdueList),
            'overdue_records' => array_slice($overdueList, 0, 10),
        ];
    }
}
