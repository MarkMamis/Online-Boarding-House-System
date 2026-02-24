<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminBookingController extends Controller
{
    protected function ensureAdmin(): void
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized');
        }
    }

    public function index(Request $request)
    {
        $this->ensureAdmin();

        $status = $request->query('status');

        $bookingsQuery = Booking::query()
            ->with(['student', 'room.property.landlord'])
            ->orderByDesc('created_at');

        if (is_string($status) && $status !== '') {
            $bookingsQuery->where('status', $status);
        }

        $bookings = $bookingsQuery->paginate(25)->withQueryString();

        $totalBookings = Booking::count();
        $pendingBookings = Booking::where('status', 'pending')->count();
        $approvedBookings = Booking::where('status', 'approved')->count();
        $rejectedBookings = Booking::where('status', 'rejected')->count();
        $cancelledBookings = Booking::where('status', 'cancelled')->count();

        $today = now()->toDateString();
        $activeTenants = Booking::where('status', 'approved')
            ->where('check_in', '<=', $today)
            ->where('check_out', '>', $today)
            ->count();

        return view('admin.bookings.index', compact(
            'bookings',
            'status',
            'totalBookings',
            'pendingBookings',
            'approvedBookings',
            'rejectedBookings',
            'cancelledBookings',
            'activeTenants'
        ));
    }
}
