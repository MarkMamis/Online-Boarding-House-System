<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    protected function ensureLandlord()
    {
        if (!Auth::check() || Auth::user()->role !== 'landlord') {
            abort(403, 'Unauthorized');
        }
    }

    public function index()
    {
        $this->ensureLandlord();
        $landlordId = Auth::id();

        // Get approved bookings with payment status
        $bookings = Booking::with(['room.property', 'student'])
            ->where('status', 'approved')
            ->whereHas('room.property', function ($q) use ($landlordId) {
                $q->where('landlord_id', $landlordId);
            })
            ->orderBy('check_in', 'desc')
            ->get();

        // Calculate payment statistics
        $totalExpected = $bookings->sum(function ($booking) {
            return $booking->room->price * $booking->getDurationInDays();
        });

        $paidBookings = $bookings->where('payment_status', 'paid');
        $totalPaid = $paidBookings->sum(function ($booking) {
            return $booking->room->price * $booking->getDurationInDays();
        });

        $pendingPayments = $bookings->where('payment_status', 'pending');
        $totalPending = $pendingPayments->sum(function ($booking) {
            return $booking->room->price * $booking->getDurationInDays();
        });

        return view('landlord.payments.index', compact(
            'bookings',
            'totalExpected',
            'totalPaid',
            'totalPending',
            'paidBookings',
            'pendingPayments'
        ));
    }

    public function markAsPaid($bookingId)
    {
        $this->ensureLandlord();
        $landlordId = Auth::id();

        $booking = Booking::where('id', $bookingId)
            ->whereHas('room.property', function ($q) use ($landlordId) {
                $q->where('landlord_id', $landlordId);
            })
            ->firstOrFail();

        $booking->update([
            'payment_status' => 'paid',
            'payment_date' => now(),
        ]);

        return back()->with('success', 'Payment marked as received.');
    }

    public function markAsPending($bookingId)
    {
        $this->ensureLandlord();
        $landlordId = Auth::id();

        $booking = Booking::where('id', $bookingId)
            ->whereHas('room.property', function ($q) use ($landlordId) {
                $q->where('landlord_id', $landlordId);
            })
            ->firstOrFail();

        $booking->update([
            'payment_status' => 'pending',
            'payment_date' => null,
        ]);

        return back()->with('success', 'Payment status set to pending.');
    }
}
