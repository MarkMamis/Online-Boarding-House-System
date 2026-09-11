<?php

namespace App\Services\AI\Tools;

use App\Models\Booking;
use App\Models\User;
use App\Services\AI\Support\AiDateRangeResolver;

class StudentTools
{
    /**
     * Count students registered in OBHS with optional date filtering (Admin only).
     */
    public function countStudents(User $user, array $args): array
    {
        $status = strtolower(trim((string) ($args['status'] ?? 'all')));
        $dateRange = AiDateRangeResolver::resolve($args);

        $baseQuery = User::where('role', 'student');
        AiDateRangeResolver::applyToQuery($baseQuery, 'created_at', $dateRange);

        $query = clone $baseQuery;
        if ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        }

        $totalInPeriod = (clone $baseQuery)->count();
        $matchingCount = $query->count();
        $verifiedCount = (clone $baseQuery)->where('school_id_verification_status', 'approved')->count();
        $pendingVerification = (clone $baseQuery)->where('school_id_verification_status', 'pending')->count();

        return [
            'period' => [
                'label' => $dateRange['label'],
                'date_from' => $dateRange['date_from'],
                'date_to' => $dateRange['date_to'],
                'date_from_formatted' => $dateRange['date_from_formatted'],
                'date_to_formatted' => $dateRange['date_to_formatted'],
                'is_filtered' => $dateRange['is_filtered'],
            ],
            'count' => $matchingCount,
            'total_students_in_period' => $totalInPeriod,
            'verified_students' => $verifiedCount,
            'pending_verification' => $pendingVerification,
            'all_time_total' => User::where('role', 'student')->count(),
            'status_filter' => $status,
            'action_url' => '/admin/student-verifications',
            'action_label' => 'Review Students',
        ];
    }

    /**
     * Aggregate statistics on students with optional registration date filtering and grouping (Admin only).
     */
    public function getStudentStatistics(User $user, array $args): array
    {
        $dateRange = AiDateRangeResolver::resolve($args);

        $baseQuery = User::where('role', 'student');
        AiDateRangeResolver::applyToQuery($baseQuery, 'created_at', $dateRange);

        $totalInPeriod = (clone $baseQuery)->count();
        $activeCount = (clone $baseQuery)->where('is_active', true)->count();
        $verifiedCount = (clone $baseQuery)->where('school_id_verification_status', 'approved')->count();
        $pendingVerification = (clone $baseQuery)->where('school_id_verification_status', 'pending')->count();
        $rejectedVerification = (clone $baseQuery)->where('school_id_verification_status', 'rejected')->count();

        // Count actively boarded students across system
        $today = now()->toDateString();
        $activelyBoarded = Booking::where('status', 'approved')
            ->whereDate('check_in', '<=', $today)
            ->whereDate('check_out', '>', $today)
            ->distinct('student_id')
            ->count('student_id');

        $result = [
            'period' => [
                'label' => $dateRange['label'],
                'date_from' => $dateRange['date_from'],
                'date_to' => $dateRange['date_to'],
                'date_from_formatted' => $dateRange['date_from_formatted'],
                'date_to_formatted' => $dateRange['date_to_formatted'],
                'is_filtered' => $dateRange['is_filtered'],
            ],
            'total_students' => $totalInPeriod,
            'new_students' => $totalInPeriod,
            'total_registered_in_period' => $totalInPeriod,
            'active_accounts' => $activeCount,
            'verified_student_ids' => $verifiedCount,
            'pending_verifications' => $pendingVerification,
            'rejected_verifications' => $rejectedVerification,
            'actively_boarded_students' => $activelyBoarded,
            'all_time_total' => User::where('role', 'student')->count(),
            'all_time_pending_verifications' => User::where('role', 'student')->where('school_id_verification_status', 'pending')->count(),
            'action_url' => '/admin/student-verifications',
            'action_label' => 'Review Students',
        ];

        $groupByInput = strtolower(trim((string) ($args['group_by'] ?? '')));
        $columnMap = [
            'college' => 'college',
            'program' => 'program',
            'course' => 'program',
            'verification_status' => 'school_id_verification_status',
            'school_id_verification_status' => 'school_id_verification_status',
            'year_level' => 'year_level',
            'gender' => 'gender',
            'onboarding_status' => 'onboarding_complete',
            'onboarding_complete' => 'onboarding_complete',
        ];

        $groupByKey = $columnMap[$groupByInput] ?? null;

        if ($groupByKey !== null) {
            if ($groupByKey === 'onboarding_complete') {
                $rawSql = "CASE WHEN onboarding_complete = 1 THEN 'Completed' ELSE 'Incomplete' END as label, COUNT(*) as count";
            } elseif ($groupByKey === 'school_id_verification_status') {
                $rawSql = "COALESCE(NULLIF(TRIM(school_id_verification_status), ''), 'pending') as label, COUNT(*) as count";
            } else {
                $rawSql = "COALESCE(NULLIF(TRIM({$groupByKey}), ''), 'Not specified') as label, COUNT(*) as count";
            }

            $groups = (clone $baseQuery)
                ->selectRaw($rawSql)
                ->groupBy('label')
                ->orderByDesc('count')
                ->get()
                ->map(fn ($r) => [
                    'label' => (string) $r->label,
                    'count' => (int) $r->count,
                ])
                ->values()
                ->all();

            $groupedTotal = (int) array_sum(array_column($groups, 'count'));
            $unspecifiedCount = 0;
            foreach ($groups as $g) {
                if (in_array(strtolower($g['label']), ['not specified', 'incomplete', 'unknown', 'n/a', 'none'], true)) {
                    $unspecifiedCount += $g['count'];
                }
            }

            $result['group_by'] = $groupByInput;
            $result['groups'] = $groups;
            $result['grouped_records_total'] = $groupedTotal;
            $result['unspecified_count'] = $unspecifiedCount;
            $result['reconciliation'] = [
                'reconciled' => ($groupedTotal === $totalInPeriod),
                'expected_total' => $totalInPeriod,
                'sum_of_groups' => $groupedTotal,
            ];
        } else {
            $result['available_groupings'] = ['college', 'program', 'verification_status', 'year_level', 'gender'];
        }

        return $result;
    }

    /**
     * Historical student registration trends and deterministic growth metrics (Admin only).
     */
    public function getStudentRegistrationTrend(User $user, array $args): array
    {
        $isSqlite = \Illuminate\Support\Facades\DB::getDriverName() === 'sqlite';

        $groupBy = strtolower(trim((string) ($args['group_by'] ?? 'month')));
        if (!in_array($groupBy, ['day', 'week', 'month'], true)) {
            $groupBy = 'month';
        }

        $dateRange = AiDateRangeResolver::resolve($args);
        $baseQuery = User::where('role', 'student');

        if ($dateRange['is_filtered']) {
            AiDateRangeResolver::applyToQuery($baseQuery, 'created_at', $dateRange);
        }

        if ($groupBy === 'day') {
            $formatSql = $isSqlite ? "strftime('%Y-%m-%d', created_at)" : "DATE_FORMAT(created_at, '%Y-%m-%d')";
        } elseif ($groupBy === 'week') {
            $formatSql = $isSqlite ? "strftime('%Y-W%W', created_at)" : "DATE_FORMAT(created_at, '%x-W%v')";
        } else {
            $formatSql = $isSqlite ? "strftime('%Y-%m', created_at)" : "DATE_FORMAT(created_at, '%Y-%m')";
        }

        $seriesRows = (clone $baseQuery)
            ->selectRaw("{$formatSql} as period_key, COUNT(*) as count")
            ->groupBy('period_key')
            ->orderBy('period_key')
            ->get();

        $series = $seriesRows->map(fn ($r) => [
            'period' => (string) $r->period_key,
            'count' => (int) $r->count,
        ])->values()->all();

        $totalRegistrations = (int) (clone $baseQuery)->count();
        $periodCount = count($series);
        $averagePerPeriod = $periodCount > 0 ? round($totalRegistrations / $periodCount, 1) : 0.0;

        // Current and recent pace indicators
        $now = now();
        $recent7Days = User::where('role', 'student')
            ->where('created_at', '>=', $now->copy()->subDays(7))
            ->count();
        $recent30Days = User::where('role', 'student')
            ->where('created_at', '>=', $now->copy()->subDays(30))
            ->count();
        $currentMonth = User::where('role', 'student')
            ->where('created_at', '>=', $now->copy()->startOfMonth())
            ->count();
        $previousMonth = User::where('role', 'student')
            ->whereBetween('created_at', [
                $now->copy()->subMonthNoOverflow()->startOfMonth(),
                $now->copy()->subMonthNoOverflow()->endOfMonth(),
            ])
            ->count();

        // Historical depth for confidence
        $monthFormatSql = $isSqlite ? "strftime('%Y-%m', created_at)" : "DATE_FORMAT(created_at, '%Y-%m')";
        $distinctMonths = User::where('role', 'student')
            ->selectRaw("{$monthFormatSql} as m")
            ->distinct()
            ->pluck('m')
            ->count();

        if ($distinctMonths < 3 || $totalRegistrations < 10) {
            $confidence = 'low';
            $forecastGuidance = 'Historical data is sparse (< 3 distinct months or < 10 records). State low confidence and summarize observed trend only. Do NOT make confident forecasts.';
        } elseif ($distinctMonths < 6 || $totalRegistrations < 30) {
            $confidence = 'medium';
            $forecastGuidance = 'Moderate historical data available. May provide rough trend estimates, but clearly label them as estimates based on recent pace, not guaranteed counts.';
        } else {
            $confidence = 'high';
            $forecastGuidance = 'Sufficient historical depth available. Trend analysis is well-grounded. Distinguish observed facts from forward-looking estimates.';
        }

        // Simple deterministic trend direction
        if ($periodCount < 2 || $totalRegistrations < 3) {
            $trendDirection = 'sparse';
        } elseif ($currentMonth > $previousMonth) {
            $trendDirection = 'increasing';
        } elseif ($currentMonth < $previousMonth) {
            $trendDirection = 'declining';
        } else {
            $trendDirection = 'stable';
        }

        return [
            'total_registrations' => $totalRegistrations,
            'period' => [
                'label' => $dateRange['label'] ?? 'All Time',
                'date_from' => $dateRange['date_from'] ?? null,
                'date_to' => $dateRange['date_to'] ?? null,
                'is_filtered' => $dateRange['is_filtered'] ?? false,
            ],
            'group_by' => $groupBy,
            'series' => $series,
            'recent_7_days' => $recent7Days,
            'recent_30_days' => $recent30Days,
            'current_month' => $currentMonth,
            'previous_month' => $previousMonth,
            'average_per_period' => $averagePerPeriod,
            'trend_direction' => $trendDirection,
            'confidence' => $confidence,
            'historical_months_count' => $distinctMonths,
            'forecast_guidance' => $forecastGuidance,
            'action_url' => '/admin/student-verifications',
            'action_label' => 'Review Students',
        ];
    }

    /**
     * Get list of recently registered students with optional date filtering (Admin only).
     */
    public function getRecentStudents(User $user, array $args): array
    {
        $limit = min(max((int) ($args['limit'] ?? 5), 1), 15);
        $verificationStatus = strtolower(trim((string) ($args['verification_status'] ?? 'all')));
        $dateRange = AiDateRangeResolver::resolve($args);

        $query = User::where('role', 'student');
        AiDateRangeResolver::applyToQuery($query, 'created_at', $dateRange);

        if ($verificationStatus !== '' && $verificationStatus !== 'all') {
            $query->where('school_id_verification_status', $verificationStatus);
        }

        $totalMatching = (clone $query)->count();

        $students = $query->latest('id')
            ->take($limit)
            ->get()
            ->map(fn ($s) => [
                'id' => $s->id,
                'name' => $s->full_name ?: $s->name,
                'email' => $s->email,
                'college' => $s->college ?: 'N/A',
                'program' => $s->program ?: 'N/A',
                'verification_status' => $s->school_id_verification_status ?: 'pending',
                'registered_at' => $s->created_at?->format('M d, Y h:i A'),
            ])
            ->all();

        return [
            'period' => [
                'label' => $dateRange['label'],
                'date_from' => $dateRange['date_from'],
                'date_to' => $dateRange['date_to'],
                'is_filtered' => $dateRange['is_filtered'],
            ],
            'total_students' => $totalMatching,
            'displayed_count' => count($students),
            'students' => $students,
            'action_url' => '/admin/student-verifications',
            'action_label' => 'Review Students',
        ];
    }

    /**
     * Get boarding status for current student (or queried student for admin).
     */
    public function getStudentBoardingStatus(User $user, array $args): array
    {
        $targetUserId = $user->id;

        if ($user->role === 'admin' && !empty($args['student_id']) && is_numeric($args['student_id'])) {
            $targetUserId = (int) $args['student_id'];
        }

        $targetUser = $targetUserId === $user->id ? $user : User::find($targetUserId);
        if (!$targetUser || $targetUser->role !== 'student') {
            return ['error' => 'Student not found.'];
        }

        $today = now()->toDateString();
        $activeBooking = Booking::with(['room.property.landlord'])
            ->where('student_id', $targetUserId)
            ->where('status', 'approved')
            ->whereDate('check_in', '<=', $today)
            ->whereDate('check_out', '>', $today)
            ->latest('check_in')
            ->first();

        if (!$activeBooking) {
            // Check if there is a future approved booking
            $upcomingBooking = Booking::with(['room.property.landlord'])
                ->where('student_id', $targetUserId)
                ->where('status', 'approved')
                ->whereDate('check_in', '>', $today)
                ->latest('check_in')
                ->first();

            if ($upcomingBooking) {
                return [
                    'status' => 'upcoming',
                    'message' => 'You have an approved booking starting soon.',
                    'property_name' => $upcomingBooking->room?->property?->name ?? 'Unknown',
                    'room_number' => $upcomingBooking->room?->room_number ?? 'N/A',
                    'check_in' => $upcomingBooking->check_in?->toFormattedDateString(),
                    'check_out' => $upcomingBooking->check_out?->toFormattedDateString(),
                ];
            }

            return [
                'status' => 'not_boarded',
                'message' => 'No active or upcoming approved boarding stay found.',
            ];
        }

        return [
            'status' => 'active_boarded',
            'booking_id' => $activeBooking->id,
            'property_name' => $activeBooking->room?->property?->name ?? 'Unknown',
            'property_address' => $activeBooking->room?->property?->address ?? 'N/A',
            'room_number' => $activeBooking->room?->room_number ?? 'N/A',
            'landlord_name' => $activeBooking->room?->property?->landlord?->name ?? 'N/A',
            'occupancy_mode' => $activeBooking->occupancy_mode ?? 'standard',
            'monthly_rent' => (float) ($activeBooking->monthly_rent_amount ?: $activeBooking->room?->price ?: 0),
            'check_in' => $activeBooking->check_in?->toFormattedDateString(),
            'check_out' => $activeBooking->check_out?->toFormattedDateString(),
            'payment_status' => $activeBooking->derivedPaymentStatus(),
        ];
    }

    /**
     * Get booking requests and status for the current student.
     */
    public function getStudentBookingStatus(User $user, array $args): array
    {
        $bookings = Booking::with(['room.property'])
            ->where('student_id', $user->id)
            ->latest()
            ->take(5)
            ->get();

        if ($bookings->isEmpty()) {
            return [
                'has_bookings' => false,
                'message' => 'You have not submitted any booking requests yet.',
                'action_url' => '/student/rooms',
            ];
        }

        $list = $bookings->map(fn ($b) => [
            'booking_id' => $b->id,
            'status' => $b->status,
            'property_name' => $b->room?->property?->name ?? 'Unknown',
            'room_number' => $b->room?->room_number ?? 'N/A',
            'check_in' => $b->check_in?->toFormattedDateString(),
            'check_out' => $b->check_out?->toFormattedDateString(),
            'monthly_rent' => (float) ($b->monthly_rent_amount ?: $b->room?->price ?: 0),
            'created_at' => $b->created_at?->toFormattedDateString(),
        ])->all();

        $latest = $bookings->first();

        return [
            'has_bookings' => true,
            'latest_booking_status' => $latest->status,
            'latest_booking' => [
                'booking_id' => $latest->id,
                'status' => $latest->status,
                'property_name' => $latest->room?->property?->name,
                'room_number' => $latest->room?->room_number,
            ],
            'recent_bookings' => $list,
        ];
    }

    /**
     * Get tenant onboarding progress for the student.
     */
    public function getStudentOnboardingStatus(User $user, array $args): array
    {
        $latestApprovedBooking = Booking::with(['tenantOnboarding', 'room.property'])
            ->where('student_id', $user->id)
            ->where('status', 'approved')
            ->latest('id')
            ->first();

        if (!$latestApprovedBooking) {
            return [
                'status' => 'no_approved_booking',
                'message' => 'You do not have an approved booking that requires onboarding.',
            ];
        }

        $onboarding = $latestApprovedBooking->tenantOnboarding;
        if (!$onboarding) {
            return [
                'status' => 'pending_start',
                'message' => 'Your booking is approved, but onboarding has not started yet. Go to /student/onboarding.',
                'action_url' => '/student/onboarding',
            ];
        }

        return [
            'booking_id' => $latestApprovedBooking->id,
            'property_name' => $latestApprovedBooking->room?->property?->name ?? 'Unknown',
            'onboarding_status' => $onboarding->status,
            'contract_signed_by_student' => (bool) $onboarding->contract_signed,
            'contract_signed_by_landlord' => (bool) $onboarding->landlord_contract_signed,
            'deposit_paid' => (bool) $onboarding->deposit_paid,
            'deposit_amount' => (float) ($onboarding->deposit_amount ?: 0),
            'action_url' => '/student/onboarding',
        ];
    }
}
