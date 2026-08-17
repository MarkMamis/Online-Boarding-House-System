<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Property;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
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

    public function boardedStudents(Request $request, \App\Services\BoardingMonitoringService $monitoringService)
    {
        $this->ensureAdmin();

        $search = trim((string) $request->query('search', ''));
        $boardingHouse = $request->query('boarding_house', $request->query('property_id', ''));
        $boardingHouse = is_scalar($boardingHouse) ? trim((string) $boardingHouse) : '';
        $propertyId = ctype_digit($boardingHouse) ? (int) $boardingHouse : 0;

        $college = trim((string) $request->query('college', ''));
        $program = trim((string) $request->query('program', ''));

        $allowedStatuses = ['all', 'active', 'checked_out', 'pending', 'cancelled'];
        $statusFilter = strtolower(trim((string) $request->query('status', 'all')));
        if (!in_array($statusFilter, $allowedStatuses, true)) {
            $statusFilter = 'all';
        }

        $rawMonth = $request->query('month');
        $rawYear = $request->query('year');

        $period = $monitoringService->resolveReportingPeriod(
            is_numeric($rawMonth) ? (int) $rawMonth : null,
            is_numeric($rawYear) ? (int) $rawYear : null
        );

        $periodStart = $period['periodStart'];
        $periodEnd = $period['periodEnd'];
        $periodLabel = $period['periodLabel'];
        $month = $period['month'];
        $year = $period['year'];

        $filters = [
            'search' => $search,
            'property_id' => $propertyId,
            'college' => $college,
            'program' => $program,
            'periodStart' => $periodStart,
            'periodEnd' => $periodEnd,
        ];

        $baseQuery = $monitoringService->buildBaseQuery($filters);

        // Filtered query for pagination
        $filteredQuery = clone $baseQuery;
        if ($statusFilter !== 'all') {
            $monitoringService->applyStatusFilter($filteredQuery, $statusFilter, now(), $periodStart, $periodEnd);
        }

        $boardedStudents = $filteredQuery
            ->orderByDesc('check_in')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        // Summaries and distributions computed from the exact same common base query
        $metrics = $monitoringService->getSummaryMetrics($baseQuery, now(), $periodStart, $periodEnd);
        $collegeDistribution = $monitoringService->getCollegeDistribution($baseQuery);
        $programDistribution = $monitoringService->getProgramDistribution($baseQuery);
        $propertyDistribution = $monitoringService->getPropertyDistribution($baseQuery);
        $filterOptions = $monitoringService->getFilterOptions();

        $boardingHouses = $filterOptions['boardingHouses'];
        $colleges = $filterOptions['colleges'];
        $programs = $filterOptions['programs'];
        $catalogPrograms = $filterOptions['catalogPrograms'];
        $years = $filterOptions['years'];

        // Map metrics variables for view compatibility
        $totalRecords = $metrics['total_records'];
        $uniqueStudents = $metrics['unique_students'];
        $activeBoardings = $metrics['active_boardings'];
        $activeTenants = $metrics['active_tenants'];
        $checkedOutBoardings = $metrics['checked_out_boardings'];
        $checkedOutTenants = $metrics['checked_out_tenants'];
        $pendingBoardings = $metrics['pending_boardings'];
        $cancelledBoardings = $metrics['cancelled_boardings'];
        $activeRooms = $metrics['active_rooms'];
        $activeProperties = $metrics['active_properties'];

        return view('admin.boarded_students.index', compact(
            'boardedStudents',
            'search',
            'boardingHouse',
            'college',
            'program',
            'month',
            'year',
            'statusFilter',
            'boardingHouses',
            'colleges',
            'programs',
            'catalogPrograms',
            'years',
            'periodStart',
            'periodEnd',
            'periodLabel',
            'totalRecords',
            'uniqueStudents',
            'activeBoardings',
            'activeTenants',
            'checkedOutBoardings',
            'checkedOutTenants',
            'pendingBoardings',
            'cancelledBoardings',
            'activeRooms',
            'activeProperties',
            'collegeDistribution',
            'programDistribution',
            'propertyDistribution'
        ));
    }
}
