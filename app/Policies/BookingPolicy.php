<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    public function before(User $user, string $ability): bool|null
    {
        return $user->role === 'admin' ? true : null;
    }

    public function view(User $user, Booking $booking): bool
    {
        if ($user->role === 'student') {
            return (int) $booking->student_id === (int) $user->id;
        }

        if ($user->role === 'landlord') {
            $booking->loadMissing('room.property');
            return $booking->room && $booking->room->property && (int) $booking->room->property->landlord_id === (int) $user->id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->role === 'student';
    }

    public function cancel(User $user, Booking $booking): bool
    {
        return $user->role === 'student' && (int) $booking->student_id === (int) $user->id;
    }

    public function approve(User $user, Booking $booking): bool
    {
        if ($user->role !== 'landlord') {
            return false;
        }

        $booking->loadMissing('room.property');
        return $booking->room && $booking->room->property && (int) $booking->room->property->landlord_id === (int) $user->id;
    }

    public function reject(User $user, Booking $booking): bool
    {
        return $this->approve($user, $booking);
    }
}
