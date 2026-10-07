<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Services\AdminReportingService;
use App\Services\AnalyticsService;
use App\Services\BoardingMonitoringService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminInstitutionalReportController extends Controller
{
    public function __construct(
        protected AdminReportingService $reportingService,
        protected BoardingMonitoringService $monitoringService,
        protected AnalyticsService $analyticsService
    ) {
    }

    protected function ensureAdmin(): void
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized. Admin access required.');
        }
    }

    /**
     * Resolve and normalize all reporting period & entity filters.
     *
     * @return array{
     *     periodInfo: array<string, mixed>,
     *     filters: array<string, mixed>,
     *     selectedPreset: string
     * }
     */
    protected function resolveRequestFilters(Request $request): array
    {
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

        $propertyId = $request->filled('property_id') ? (int) $request->query('property_id') : null;
        $college = $request->query('college');
        $program = $request->query('program');

        $filters = array_merge($periodInfo, [
            'property_id' => $propertyId,
            'college' => $college,
            'program' => $program,
        ]);

        $selectedPreset = (string) $request->query('report_preset', AdminReportingService::PRESET_ACTIVE_BOARDERS);
        $presetDefinitions = $this->reportingService->getPresetDefinitions();

        if (!array_key_exists($selectedPreset, $presetDefinitions)) {
            $selectedPreset = AdminReportingService::PRESET_ACTIVE_BOARDERS;
        }

        return [
            'periodInfo' => $periodInfo,
            'filters' => $filters,
            'selectedPreset' => $selectedPreset,
        ];
    }

    public function index(Request $request)
    {
        $this->ensureAdmin();

        $resolved = $this->resolveRequestFilters($request);
        $periodInfo = $resolved['periodInfo'];
        $filters = $resolved['filters'];
        $selectedPreset = $resolved['selectedPreset'];

        $reportPresets = $this->reportingService->getPresetDefinitions();
        $presetData = $this->reportingService->getReportData($selectedPreset, $filters);
        $filterOptions = $this->monitoringService->getFilterOptions();

        $propertyId = $filters['property_id'];
        $college = $filters['college'];
        $program = $filters['program'];

        $reportData = $presetData;

        return view('admin.institutional_reports.index', compact(
            'reportPresets',
            'selectedPreset',
            'presetData',
            'reportData',
            'filterOptions',
            'periodInfo',
            'propertyId',
            'college',
            'program'
        ));
    }

    public function exportCsv(Request $request)
    {
        $this->ensureAdmin();

        $resolved = $this->resolveRequestFilters($request);
        $filters = $resolved['filters'];
        $selectedPreset = $resolved['selectedPreset'];

        $reportData = $this->reportingService->getReportData($selectedPreset, $filters);

        $filename = 'minsu_obhs_' . $selectedPreset . '_' . now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($reportData) {
            $handle = fopen('php://output', 'w');
            // Add UTF-8 BOM
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Header row
            fputcsv($handle, $reportData['columns']);

            $sanitize = fn ($str) => preg_match('/^[=\+\-\@]/', (string) $str) ? "'" . $str : (string) $str;

            foreach ($reportData['rows'] as $row) {
                $rowArray = is_array($row) ? $row : (array) $row;
                $sanitizedRow = array_map($sanitize, array_values($rowArray));
                fputcsv($handle, $sanitizedRow);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function printReport(Request $request)
    {
        $this->ensureAdmin();

        $resolved = $this->resolveRequestFilters($request);
        $periodInfo = $resolved['periodInfo'];
        $filters = $resolved['filters'];
        $selectedPreset = $resolved['selectedPreset'];

        $reportPresets = $this->reportingService->getPresetDefinitions();
        $presetMeta = $reportPresets[$selectedPreset] ?? [
            'title' => 'Institutional Report',
            'description' => '',
        ];

        $reportData = $this->reportingService->getReportData($selectedPreset, $filters);

        // Resolve human-readable labels for print header
        $selectedProperty = $filters['property_id'] ? Property::find($filters['property_id']) : null;
        $propertyLabel = $selectedProperty ? $selectedProperty->name : 'All Boarding Houses';
        $collegeLabel = !empty($filters['college']) ? $filters['college'] : 'All Colleges';
        $programLabel = !empty($filters['program']) ? $filters['program'] : 'All Programs';
        $periodLabel = $periodInfo['periodLabel'] ?? 'All Time (Current Active)';

        return view('admin.institutional_reports.print', compact(
            'selectedPreset',
            'presetMeta',
            'reportData',
            'periodInfo',
            'propertyLabel',
            'collegeLabel',
            'programLabel',
            'periodLabel'
        ));
    }
}
