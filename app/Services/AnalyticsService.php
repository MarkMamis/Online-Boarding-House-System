<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\LandlordDocument;
use App\Models\Property;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AnalyticsService
{
    public function __construct(
        protected BoardingMonitoringService $monitoringService,
        protected DocumentExpirationService $expirationService
    ) {
    }

    /**
     * Get high-level summary KPIs.
     *
     * @param array<string, mixed> $periodInfo
     */
    public function getKpis(array $periodInfo, Builder $baseQuery): array
    {
        $periodStart = $periodInfo['periodStart'] ?? null;
        $periodEnd = $periodInfo['periodEnd'] ?? null;
        $dateBasis = $periodInfo['dateBasis'] ?? 'stay';

        // 1. Current Active Boarders using canonical distinct counting
        $monitoringMetrics = $this->monitoringService->getSummaryMetrics(
            $baseQuery,
            now(),
            $periodStart,
            $periodEnd,
            $dateBasis
        );

        // 2. Registered Users
        $registeredStudents = User::query()->where('role', 'student')->count();
        $registeredLandlords = User::query()->where('role', 'landlord')->count();

        // 3. Properties & Rooms
        $approvedProperties = Property::query()->where('approval_status', 'approved')->count();
        $totalProperties = Property::query()->count();

        $roomCounts = Room::query()
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status = 'occupied' THEN 1 ELSE 0 END) as occupied,
                SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) as available,
                SUM(CASE WHEN status = 'maintenance' THEN 1 ELSE 0 END) as maintenance,
                COALESCE(SUM(capacity), 0) as capacity,
                COALESCE(SUM(slots_available), 0) as slots_available
            ")
            ->first();

        $totalRooms = (int) ($roomCounts->total ?? 0);
        $occupiedRooms = (int) ($roomCounts->occupied ?? 0);
        $availableRooms = (int) ($roomCounts->available ?? 0);
        $maintenanceRooms = (int) ($roomCounts->maintenance ?? 0);
        $totalCapacity = (int) ($roomCounts->capacity ?? 0);
        $slotsAvailable = (int) ($roomCounts->slots_available ?? 0);

        $roomOccupancyRate = $totalRooms > 0
            ? round(($occupiedRooms / $totalRooms) * 100, 1)
            : 0;

        $occupiedBeds = max(0, $totalCapacity - $slotsAvailable);
        $bedOccupancyRate = $totalCapacity > 0
            ? round(($occupiedBeds / $totalCapacity) * 100, 1)
            : 0;

        // 4. Pending Action Items
        $pendingBookings = Booking::query()->where('status', 'pending')->count();
        $pendingDocuments = Schema::hasTable('landlord_documents')
            ? LandlordDocument::query()->current()->where('verification_status', 'pending')->count()
            : 0;

        return [
            'active_boarders' => (int) $monitoringMetrics['active_tenants'],
            'total_stay_records' => (int) $monitoringMetrics['total_records'],
            'registered_students' => $registeredStudents,
            'registered_landlords' => $registeredLandlords,
            'approved_properties' => $approvedProperties,
            'total_properties' => $totalProperties,
            'total_rooms' => $totalRooms,
            'occupied_rooms' => $occupiedRooms,
            'available_rooms' => $availableRooms,
            'maintenance_rooms' => $maintenanceRooms,
            'total_capacity' => $totalCapacity,
            'occupied_beds' => $occupiedBeds,
            'slots_available' => $slotsAvailable,
            'room_occupancy_rate' => $roomOccupancyRate,
            'bed_occupancy_rate' => $bedOccupancyRate,
            'pending_bookings' => $pendingBookings,
            'pending_documents' => $pendingDocuments,
        ];
    }

    /**
     * 12-month boarding and booking trend time-series for a selected year.
     *
     * @return array{
     *     year: int,
     *     labels: array<int, string>,
     *     active_boarders: array<int, int>,
     *     new_bookings: array<int, int>
     * }
     */
    public function getBoardingTrend(int $year, ?int $propertyId = null): array
    {
        $labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $activeBoardersSeries = [];
        $newBookingsSeries = [];

        for ($month = 1; $month <= 12; $month++) {
            $startOfMonth = Carbon::create($year, $month, 1)->startOfDay();
            $endOfMonth = $startOfMonth->copy()->endOfMonth()->endOfDay();

            // 1. Active boarders overlapping this specific month
            $activeQuery = Booking::query()
                ->whereHas('student', fn (Builder $q) => $q->where('role', 'student'))
                ->when($propertyId > 0, function (Builder $q) use ($propertyId) {
                    $q->whereHas('room.property', fn (Builder $pq) => $pq->whereKey($propertyId));
                });

            $this->monitoringService->applyPeriodFilter($activeQuery, $startOfMonth, $endOfMonth, 'stay');
            $this->monitoringService->applyStatusFilter($activeQuery, 'active', null, $startOfMonth, $endOfMonth, 'stay');

            $activeCount = (clone $activeQuery)->distinct('bookings.student_id')->count('bookings.student_id');
            $activeBoardersSeries[] = $activeCount;

            // 2. New booking requests submitted in this month
            $newBookingQuery = Booking::query()
                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->when($propertyId > 0, function (Builder $q) use ($propertyId) {
                    $q->whereHas('room.property', fn (Builder $pq) => $pq->whereKey($propertyId));
                });

            $newBookingsSeries[] = $newBookingQuery->count();
        }

        return [
            'year' => $year,
            'labels' => $labels,
            'active_boarders' => $activeBoardersSeries,
            'new_bookings' => $newBookingsSeries,
        ];
    }

    /**
     * Get student demographic breakdowns: College, Program, and Gender distributions.
     */
    public function getStudentDemographics(Builder $baseQuery): array
    {
        // 1. College Distribution
        $collegeDist = $this->monitoringService->getCollegeDistribution($baseQuery);

        // 2. Program Distribution (Top 10 programs)
        $programDist = $this->monitoringService->getProgramDistribution($baseQuery)->take(10);

        // 3. Gender Distribution for active boarders (strict SQL via subquery)
        $boarderGenderSubquery = (clone $baseQuery)
            ->toBase()
            ->join('users', 'users.id', '=', 'bookings.student_id')
            ->select(
                'bookings.student_id',
                DB::raw("CASE
                    WHEN LOWER(TRIM(COALESCE(users.gender, ''))) = 'male' THEN 'male'
                    WHEN LOWER(TRIM(COALESCE(users.gender, ''))) = 'female' THEN 'female'
                    ELSE 'unspecified'
                END as normalized_gender")
            )
            ->distinct();

        $boarderGenderCounts = DB::query()
            ->fromSub($boarderGenderSubquery, 'bg')
            ->select('normalized_gender', DB::raw('COUNT(*) as total'))
            ->groupBy('normalized_gender')
            ->pluck('total', 'normalized_gender')
            ->all();

        $boarderGender = [
            'male' => (int) ($boarderGenderCounts['male'] ?? 0),
            'female' => (int) ($boarderGenderCounts['female'] ?? 0),
            'unspecified' => (int) ($boarderGenderCounts['unspecified'] ?? 0),
        ];

        // 4. Gender Distribution for all registered students
        $registeredGenderSubquery = User::query()
            ->where('role', 'student')
            ->select('id', DB::raw("CASE
                WHEN LOWER(TRIM(COALESCE(gender, ''))) = 'male' THEN 'male'
                WHEN LOWER(TRIM(COALESCE(gender, ''))) = 'female' THEN 'female'
                ELSE 'unspecified'
            END as normalized_gender"));

        $registeredGenderCounts = DB::query()
            ->fromSub($registeredGenderSubquery, 'rg')
            ->select('normalized_gender', DB::raw('COUNT(*) as total'))
            ->groupBy('normalized_gender')
            ->pluck('total', 'normalized_gender')
            ->all();

        $registeredGender = [
            'male' => (int) ($registeredGenderCounts['male'] ?? 0),
            'female' => (int) ($registeredGenderCounts['female'] ?? 0),
            'unspecified' => (int) ($registeredGenderCounts['unspecified'] ?? 0),
        ];

        return [
            'colleges' => $collegeDist,
            'programs' => $programDist,
            'boarder_gender' => $boarderGender,
            'registered_gender' => $registeredGender,
        ];
    }

    /**
     * Property and room occupancy details.
     *
     * @return Collection<int, object>
     */
    public function getPropertyOccupancyAnalytics(?int $propertyId = null): Collection
    {
        return Property::query()
            ->when($propertyId > 0, fn (Builder $q) => $q->whereKey($propertyId))
            ->where('approval_status', 'approved')
            ->with('landlord:id,full_name,name,email,contact_number')
            ->withCount([
                'rooms as total_rooms',
                'rooms as occupied_rooms' => fn (Builder $q) => $q->where('status', 'occupied'),
                'rooms as available_rooms' => fn (Builder $q) => $q->where('status', 'available'),
                'rooms as maintenance_rooms' => fn (Builder $q) => $q->where('status', 'maintenance'),
            ])
            ->withSum('rooms as total_capacity', 'capacity')
            ->withSum('rooms as total_slots_available', 'slots_available')
            ->orderBy('name')
            ->get()
            ->map(function ($property) {
                $totalRooms = (int) $property->total_rooms;
                $occupiedRooms = (int) $property->occupied_rooms;
                $availableRooms = (int) $property->available_rooms;
                $maintenanceRooms = (int) $property->maintenance_rooms;
                $totalCapacity = (int) ($property->total_capacity ?? 0);
                $slotsAvailable = (int) ($property->total_slots_available ?? 0);
                $occupiedBeds = max(0, $totalCapacity - $slotsAvailable);

                $roomOccupancyRate = $totalRooms > 0
                    ? round(($occupiedRooms / $totalRooms) * 100, 1)
                    : 0;

                $bedOccupancyRate = $totalCapacity > 0
                    ? round(($occupiedBeds / $totalCapacity) * 100, 1)
                    : 0;

                return (object) [
                    'id' => (int) $property->id,
                    'name' => (string) $property->name,
                    'address' => (string) ($property->address ?? 'Address not set'),
                    'landlord_name' => (string) ($property->landlord?->full_name ?: ($property->landlord?->name ?: 'Landlord')),
                    'landlord_email' => (string) ($property->landlord?->email ?? ''),
                    'landlord_contact' => (string) ($property->landlord?->contact_number ?? ''),
                    'total_rooms' => $totalRooms,
                    'occupied_rooms' => $occupiedRooms,
                    'available_rooms' => $availableRooms,
                    'maintenance_rooms' => $maintenanceRooms,
                    'total_capacity' => $totalCapacity,
                    'occupied_beds' => $occupiedBeds,
                    'slots_available' => $slotsAvailable,
                    'room_occupancy_rate' => $roomOccupancyRate,
                    'bed_occupancy_rate' => $bedOccupancyRate,
                    'occupancy_rate' => $roomOccupancyRate,
                ];
            });
    }

    /**
     * Booking status distribution for a specified reporting period.
     *
     * @return array<string, int>
     */
    public function getBookingStatusBreakdown(?Carbon $periodStart = null, ?Carbon $periodEnd = null): array
    {
        $query = Booking::query();

        if ($periodStart instanceof Carbon && $periodEnd instanceof Carbon) {
            $query->whereBetween('created_at', [$periodStart, $periodEnd]);
        }

        $rows = (clone $query)
            ->toBase()
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();

        $approved = (int) ($rows['approved'] ?? 0);
        $pending = (int) ($rows['pending'] ?? 0);
        $rejected = (int) ($rows['rejected'] ?? 0);
        $cancelled = (int) ($rows['cancelled'] ?? 0);
        $total = $approved + $pending + $rejected + $cancelled;

        return [
            'approved' => $approved,
            'pending' => $pending,
            'rejected' => $rejected,
            'cancelled' => $cancelled,
            'total' => $total,
        ];
    }

    /**
     * Landlord document compliance breakdown.
     *
     * @return array<string, mixed>
     */
    public function getDocumentComplianceBreakdown(): array
    {
        if (!Schema::hasTable('landlord_documents')) {
            return [
                'business_permit' => ['valid' => 0, 'expiring_soon' => 0, 'expired' => 0, 'pending' => 0, 'rejected' => 0, 'total' => 0],
                'safety_certificate' => ['valid' => 0, 'expiring_soon' => 0, 'expired' => 0, 'pending' => 0, 'rejected' => 0, 'total' => 0],
                'total_compliant_landlords' => 0,
            ];
        }

        $bpQuery = LandlordDocument::query()->current()->where('document_type', LandlordDocument::TYPE_BUSINESS_PERMIT);
        $scQuery = LandlordDocument::query()->current()->where('document_type', LandlordDocument::TYPE_SAFETY_CERTIFICATE);

        $calc = function (Builder $query) {
            $valid = (clone $query)->valid()->count();
            $expiring = (clone $query)->expiringSoon()->count();
            $expired = (clone $query)->expired()->count();
            $pending = (clone $query)->where('verification_status', LandlordDocument::STATUS_PENDING)->count();
            $rejected = (clone $query)->where('verification_status', LandlordDocument::STATUS_REJECTED)->count();
            $total = (clone $query)->count();

            return [
                'valid' => $valid,
                'expiring_soon' => $expiring,
                'expired' => $expired,
                'pending' => $pending,
                'rejected' => $rejected,
                'total' => $total,
            ];
        };

        $bpStats = $calc($bpQuery);
        $scStats = $calc($scQuery);

        // Landlords who have both approved and valid business permit & safety certificate
        $compliantLandlords = LandlordDocument::query()
            ->current()
            ->valid()
            ->whereIn('document_type', [LandlordDocument::TYPE_BUSINESS_PERMIT, LandlordDocument::TYPE_SAFETY_CERTIFICATE])
            ->select('landlord_id')
            ->groupBy('landlord_id')
            ->havingRaw('COUNT(DISTINCT document_type) = 2')
            ->get()
            ->count();

        return [
            'business_permit' => $bpStats,
            'safety_certificate' => $scStats,
            'total_compliant_landlords' => $compliantLandlords,
        ];
    }
}
