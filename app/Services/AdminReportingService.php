<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\LandlordDocument;
use App\Models\Property;
use App\Models\Report;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminReportingService
{
    public const PRESET_ACTIVE_BOARDERS = 'active_boarders';
    public const PRESET_PROPERTY_DISTRIBUTION = 'property_distribution';
    public const PRESET_ACADEMIC_DISTRIBUTION = 'academic_distribution';
    public const PRESET_OCCUPANCY_AVAILABILITY = 'occupancy_availability';
    public const PRESET_COMPLIANCE_SUMMARY = 'compliance_summary';
    public const PRESET_INCIDENT_SUMMARY = 'incident_summary';

    public function __construct(
        protected BoardingMonitoringService $monitoringService,
        protected AnalyticsService $analyticsService
    ) {
    }

    /**
     * Get list of available report presets and descriptions.
     *
     * @return array<string, array{title: string, description: string, icon: string}>
     */
    public function getPresetDefinitions(): array
    {
        return [
            self::PRESET_ACTIVE_BOARDERS => [
                'title' => 'Active Boarded Students Report',
                'description' => 'De-duplicated listing of students currently residing in accredited boarding houses.',
                'icon' => 'bi-person-check',
            ],
            self::PRESET_PROPERTY_DISTRIBUTION => [
                'title' => 'Boarding House Distribution Report',
                'description' => 'Aggregate summary of active boarders, capacity, and bed occupancy by property.',
                'icon' => 'bi-buildings',
            ],
            self::PRESET_ACADEMIC_DISTRIBUTION => [
                'title' => 'Academic Distribution Report',
                'description' => 'Breakdown of accommodated students by college and academic program.',
                'icon' => 'bi-mortarboard',
            ],
            self::PRESET_OCCUPANCY_AVAILABILITY => [
                'title' => 'Occupancy and Availability Report',
                'description' => 'Property-level room inventory, vacant slots, and occupancy percentages.',
                'icon' => 'bi-door-open',
            ],
            self::PRESET_COMPLIANCE_SUMMARY => [
                'title' => 'Landlord Compliance Summary Report',
                'description' => 'Accreditation, business permit, and safety certificate status overview.',
                'icon' => 'bi-shield-check',
            ],
            self::PRESET_INCIDENT_SUMMARY => [
                'title' => 'Incident Reports Summary',
                'description' => 'Aggregate triage counts, severity distribution, and case resolutions.',
                'icon' => 'bi-flag',
            ],
        ];
    }

    /**
     * Retrieve structured dataset for a specified report preset.
     */
    public function getReportData(string $preset, array $filters = []): array
    {
        return match ($preset) {
            self::PRESET_ACTIVE_BOARDERS => $this->getActiveBoardersData($filters),
            self::PRESET_PROPERTY_DISTRIBUTION => $this->getPropertyDistributionData($filters),
            self::PRESET_ACADEMIC_DISTRIBUTION => $this->getAcademicDistributionData($filters),
            self::PRESET_COMPLIANCE_SUMMARY => $this->getComplianceSummaryData($filters),
            self::PRESET_INCIDENT_SUMMARY => $this->getIncidentSummaryData($filters),
            default => $this->getOccupancyAvailabilityData($filters),
        };
    }

    protected function getActiveBoardersData(array $filters): array
    {
        $today = now()->toDateString();
        $propertyId = $filters['property_id'] ?? null;
        $college = $filters['college'] ?? null;

        $query = Booking::query()
            ->where('bookings.status', 'approved')
            ->whereDate('bookings.check_in', '<=', $today)
            ->where(function ($q) use ($today) {
                $q->whereNull('bookings.check_out')
                  ->orWhereDate('bookings.check_out', '>', $today);
            })
            ->with(['student', 'room.property.landlord'])
            ->when($propertyId > 0, fn ($q) => $q->whereHas('room.property', fn ($pq) => $pq->whereKey($propertyId)))
            ->when(!empty($college), fn ($q) => $q->whereHas('student', fn ($sq) => $sq->where('college', $college)))
            ->orderBy('bookings.check_in', 'desc');

        $records = $query->get()->map(function ($b) {
            return [
                'student_name' => (string) ($b->student?->full_name ?: $b->student?->name ?: 'Student'),
                'student_id' => (string) ($b->student?->student_id ?: 'N/A'),
                'college' => (string) ($b->student?->college ?: 'Not specified'),
                'program' => (string) ($b->student?->program ?: 'Not specified'),
                'property_name' => (string) ($b->room?->property?->name ?: 'N/A'),
                'room_number' => (string) ($b->room?->room_number ?: 'N/A'),
                'check_in' => $b->check_in ? $b->check_in->format('Y-m-d') : 'N/A',
                'check_out' => $b->check_out ? $b->check_out->format('Y-m-d') : 'Open-ended',
            ];
        });

        return [
            'columns' => ['Student Name', 'Student ID', 'College', 'Program', 'Boarding House', 'Room', 'Check-In', 'Check-Out'],
            'rows' => $records,
            'summary' => [
                'Total Active Boarders' => $records->count(),
                'Unique Students' => $records->pluck('student_id')->unique()->count(),
            ],
        ];
    }

    protected function getPropertyDistributionData(array $filters): array
    {
        $occupancy = $this->analyticsService->getPropertyOccupancyAnalytics($filters['property_id'] ?? null);

        $rows = $occupancy->map(function ($p) {
            return [
                'property_name' => $p->name,
                'landlord' => $p->landlord_name,
                'total_rooms' => $p->total_rooms,
                'occupied_rooms' => $p->occupied_rooms,
                'available_rooms' => $p->available_rooms,
                'bed_capacity' => $p->total_capacity,
                'occupied_beds' => $p->occupied_beds,
                'occupancy_rate' => $p->room_occupancy_rate . '%',
            ];
        });

        return [
            'columns' => ['Boarding House', 'Landlord', 'Total Rooms', 'Occupied Rooms', 'Available Rooms', 'Bed Capacity', 'Occupied Beds', 'Occupancy Rate'],
            'rows' => $rows,
            'summary' => [
                'Total Boarding Houses' => $rows->count(),
                'Total Capacity' => $rows->sum('bed_capacity'),
                'Total Occupied Beds' => $rows->sum('occupied_beds'),
            ],
        ];
    }

    protected function getAcademicDistributionData(array $filters): array
    {
        $baseQuery = $this->monitoringService->buildBaseQuery($filters);
        $colleges = $this->monitoringService->getCollegeDistribution($baseQuery);
        $programs = $this->monitoringService->getProgramDistribution($baseQuery);

        $rows = $programs->map(function ($pr) {
            return [
                'college_code' => $pr->college_code,
                'program_name' => $pr->program_name,
                'total_students' => $pr->total_students,
            ];
        });

        return [
            'columns' => ['College Code', 'Program Name', 'Active Boarders'],
            'rows' => $rows,
            'summary' => [
                'Total Colleges' => $colleges->count(),
                'Total Programs' => $programs->count(),
                'Total Boarded Students' => $colleges->sum('total_students'),
            ],
        ];
    }

    protected function getOccupancyAvailabilityData(array $filters): array
    {
        $occupancy = $this->analyticsService->getPropertyOccupancyAnalytics($filters['property_id'] ?? null);

        $rows = $occupancy->map(function ($p) {
            return [
                'property_name' => $p->name,
                'address' => $p->address,
                'total_rooms' => $p->total_rooms,
                'occupied_rooms' => $p->occupied_rooms,
                'available_rooms' => $p->available_rooms,
                'available_slots' => $p->slots_available,
                'room_occupancy_rate' => $p->room_occupancy_rate . '%',
                'bed_occupancy_rate' => $p->bed_occupancy_rate . '%',
            ];
        });

        return [
            'columns' => ['Property Name', 'Address', 'Total Rooms', 'Occupied Rooms', 'Available Rooms', 'Available Slots', 'Room Occupancy Rate (%)', 'Bed Occupancy Rate (%)'],
            'rows' => $rows,
            'summary' => [
                'Total Properties' => $rows->count(),
                'Total Available Slots' => $rows->sum('available_slots'),
                'Total Available Rooms' => $rows->sum('available_rooms'),
            ],
        ];
    }

    protected function getComplianceSummaryData(array $filters): array
    {
        $compliance = $this->analyticsService->getDocumentComplianceBreakdown();

        $rows = collect([
            [
                'document_type' => 'Business Permit',
                'valid' => $compliance['business_permit']['valid'],
                'expiring_soon' => $compliance['business_permit']['expiring_soon'],
                'expired' => $compliance['business_permit']['expired'],
                'pending' => $compliance['business_permit']['pending'],
                'rejected' => $compliance['business_permit']['rejected'],
                'total' => $compliance['business_permit']['total'],
            ],
            [
                'document_type' => 'Safety Certificate',
                'valid' => $compliance['safety_certificate']['valid'],
                'expiring_soon' => $compliance['safety_certificate']['expiring_soon'],
                'expired' => $compliance['safety_certificate']['expired'],
                'pending' => $compliance['safety_certificate']['pending'],
                'rejected' => $compliance['safety_certificate']['rejected'],
                'total' => $compliance['safety_certificate']['total'],
            ],
        ]);

        return [
            'columns' => ['Document Type', 'Valid', 'Expiring Soon', 'Expired', 'Pending Review', 'Rejected', 'Total Submitted'],
            'rows' => $rows,
            'summary' => [
                'Fully Compliant Landlords' => $compliance['total_compliant_landlords'],
            ],
        ];
    }

    protected function getIncidentSummaryData(array $filters): array
    {
        $reports = Report::query()->latest()->get();

        $rows = collect([
            [
                'metric' => 'Pending Review',
                'count' => $reports->where('status', 'pending')->count(),
            ],
            [
                'metric' => 'In Progress / Investigating',
                'count' => $reports->where('status', 'in_progress')->count(),
            ],
            [
                'metric' => 'Resolved Cases',
                'count' => $reports->where('status', 'resolved')->count(),
            ],
            [
                'metric' => 'Critical Priority (High)',
                'count' => $reports->where('priority', 'high')->count(),
            ],
            [
                'metric' => 'Moderate Priority (Medium)',
                'count' => $reports->where('priority', 'medium')->count(),
            ],
            [
                'metric' => 'Minor Priority (Low)',
                'count' => $reports->where('priority', 'low')->count(),
            ],
        ]);

        return [
            'columns' => ['Incident Category / State', 'Total Cases'],
            'rows' => $rows,
            'summary' => [
                'Total Reports Logged' => $reports->count(),
                'Resolution Rate' => $reports->count() > 0 ? round(($reports->where('status', 'resolved')->count() / $reports->count()) * 100, 1) . '%' : '0%',
            ],
        ];
    }
}
