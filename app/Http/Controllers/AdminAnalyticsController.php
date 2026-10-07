<?php

namespace App\Http\Controllers;

use App\Services\AnalyticsService;
use App\Services\BoardingMonitoringService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminAnalyticsController extends Controller
{
    public function __construct(
        protected AnalyticsService $analyticsService,
        protected BoardingMonitoringService $monitoringService
    ) {
    }

    public function index(Request $request)
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized. Admin access required.');
        }

        // 1. Resolve reporting period boundaries and preset
        $month = $request->filled('month') ? (int) $request->query('month') : null;
        $year = $request->filled('year') ? (int) $request->query('year') : null;
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');
        $dateBasis = $request->query('date_basis', 'stay');
        $periodPreset = $request->query('period_preset', 'all');

        $periodInfo = $this->monitoringService->resolveReportingPeriod(
            $month,
            $year,
            $dateFrom,
            $dateTo,
            $dateBasis,
            $periodPreset
        );

        // 2. Resolve additional entity filters
        $propertyId = $request->filled('property_id') ? (int) $request->query('property_id') : null;
        $college = $request->query('college');
        $program = $request->query('program');

        $filters = array_merge($periodInfo, [
            'property_id' => $propertyId,
            'college' => $college,
            'program' => $program,
        ]);

        // 3. Build base canonical query
        $baseQuery = $this->monitoringService->buildBaseQuery($filters);

        // 4. Retrieve analytics datasets
        $kpis = $this->analyticsService->getKpis($periodInfo, $baseQuery);

        $trendYear = $periodInfo['year'] ?: now()->year;
        $trend = $this->analyticsService->getBoardingTrend($trendYear, $propertyId);

        $demographics = $this->analyticsService->getStudentDemographics($baseQuery);
        $occupancy = $this->analyticsService->getPropertyOccupancyAnalytics($propertyId);
        $bookingLifecycle = $this->analyticsService->getBookingStatusBreakdown(
            $periodInfo['periodStart'],
            $periodInfo['periodEnd']
        );
        $compliance = $this->analyticsService->getDocumentComplianceBreakdown();
        $filterOptions = $this->monitoringService->getFilterOptions();

        return view('admin.analytics.index', compact(
            'kpis',
            'trend',
            'demographics',
            'occupancy',
            'bookingLifecycle',
            'compliance',
            'filterOptions',
            'periodInfo',
            'propertyId',
            'college',
            'program',
            'trendYear'
        ));
    }

    public function exportCsv(Request $request)
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized. Admin access required.');
        }

        $propertyId = $request->filled('property_id') ? (int) $request->query('property_id') : null;
        $occupancy = $this->analyticsService->getPropertyOccupancyAnalytics($propertyId);

        $filename = 'minsu_obhs_occupancy_report_' . now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($occupancy) {
            $handle = fopen('php://output', 'w');
            // Add UTF-8 BOM
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // Header row
            fputcsv($handle, [
                'Property Name',
                'Address',
                'Landlord Name',
                'Landlord Email',
                'Total Rooms',
                'Occupied Rooms',
                'Available Rooms',
                'Total Bed Capacity',
                'Occupied Beds',
                'Room Occupancy Rate (%)',
                'Bed Occupancy Rate (%)',
            ]);

            foreach ($occupancy as $prop) {
                // Formula injection protection
                $sanitize = fn ($str) => preg_match('/^[=\+\-\@]/', (string) $str) ? "'" . $str : (string) $str;

                fputcsv($handle, [
                    $sanitize($prop->name),
                    $sanitize($prop->address),
                    $sanitize($prop->landlord_name),
                    $sanitize($prop->landlord_email),
                    $prop->total_rooms,
                    $prop->occupied_rooms,
                    $prop->available_rooms,
                    $prop->total_capacity,
                    $prop->occupied_beds,
                    $prop->room_occupancy_rate . '%',
                    $prop->bed_occupancy_rate . '%',
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
