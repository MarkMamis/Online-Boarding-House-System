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
        $search = trim((string) $request->query('search', ''));
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        $bookingsQuery = Booking::query()
            ->with(['student', 'room.property.landlord'])
            ->orderByDesc('created_at');

        if (is_string($status) && $status !== '') {
            $bookingsQuery->where('status', $status);
        }

        if ($search !== '') {
            $bookingsQuery->where(function ($query) use ($search) {
                if (ctype_digit($search)) {
                    $query->orWhere('id', (int) $search);
                }

                $like = '%' . $search . '%';

                $query->orWhereHas('student', function ($studentQuery) use ($like) {
                    $studentQuery->where('full_name', 'like', $like)
                        ->orWhere('email', 'like', $like);
                });

                $query->orWhereHas('room', function ($roomQuery) use ($like) {
                    $roomQuery->where('room_number', 'like', $like);
                });

                $query->orWhereHas('room.property', function ($propertyQuery) use ($like) {
                    $propertyQuery->where('name', 'like', $like)
                        ->orWhere('address', 'like', $like);
                });

                $query->orWhereHas('room.property.landlord', function ($landlordQuery) use ($like) {
                    $landlordQuery->where('full_name', 'like', $like)
                        ->orWhere('email', 'like', $like);
                });
            });
        }

        if (is_string($dateFrom) && $dateFrom !== '') {
            $bookingsQuery->whereDate('created_at', '>=', $dateFrom);
        }

        if (is_string($dateTo) && $dateTo !== '') {
            $bookingsQuery->whereDate('created_at', '<=', $dateTo);
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
            'search',
            'dateFrom',
            'dateTo',
            'totalBookings',
            'pendingBookings',
            'approvedBookings',
            'rejectedBookings',
            'cancelledBookings',
            'activeTenants'
        ));
    }

    public function boardedStudents(Request $request)
    {
        $this->ensureAdmin();

        $search = trim((string) $request->query('search', ''));
        $today = now()->toDateString();

        $applyActiveBoardingFilter = function ($query) use ($today) {
            return $query
                ->where('bookings.status', 'approved')
                ->whereDate('bookings.check_in', '<=', $today)
                ->where(function ($inner) use ($today) {
                    $inner->whereNull('bookings.check_out')
                        ->orWhereDate('bookings.check_out', '>', $today);
                });
        };

        $metricsBaseQuery = $applyActiveBoardingFilter(Booking::query());

        $activeBoardings = (clone $metricsBaseQuery)->count();
        $activeTenants = (clone $metricsBaseQuery)->distinct('bookings.student_id')->count('bookings.student_id');
        $activeRooms = (clone $metricsBaseQuery)->distinct('bookings.room_id')->count('bookings.room_id');
        $activeProperties = (clone $metricsBaseQuery)
            ->join('rooms as metric_rooms', 'metric_rooms.id', '=', 'bookings.room_id')
            ->distinct('metric_rooms.property_id')
            ->count('metric_rooms.property_id');

        $boardedStudentsQuery = $applyActiveBoardingFilter(
            Booking::query()
                ->with(['student', 'room.property'])
        );

        if ($search !== '') {
            $boardedStudentsQuery->where(function ($query) use ($search) {
                if (ctype_digit($search)) {
                    $query->orWhere('id', (int) $search)
                        ->orWhere('student_id', (int) $search);
                }

                $like = '%' . $search . '%';

                $query->orWhereHas('student', function ($studentQuery) use ($like) {
                    $studentQuery->where('full_name', 'like', $like)
                        ->orWhere('email', 'like', $like)
                        ->orWhere('contact_number', 'like', $like);
                });

                $query->orWhereHas('room', function ($roomQuery) use ($like) {
                    $roomQuery->where('room_number', 'like', $like);
                });

                $query->orWhereHas('room.property', function ($propertyQuery) use ($like) {
                    $propertyQuery->where('name', 'like', $like)
                        ->orWhere('address', 'like', $like);
                });
            });
        }

        $boardedStudents = $boardedStudentsQuery
            ->orderBy('check_in')
            ->paginate(20)
            ->withQueryString();

        return view('admin.boarded_students.index', compact(
            'boardedStudents',
            'search',
            'activeBoardings',
            'activeTenants',
            'activeRooms',
            'activeProperties'
        ));
    }
}
