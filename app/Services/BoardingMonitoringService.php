<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Property;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BoardingMonitoringService
{
    /**
     * Resolve reporting period boundaries and basis from filter inputs.
     *
     * Priority:
     * 1. Custom Date Range (date_from, date_to) if provided
     * 2. Month + Year or Year if provided
     * 3. Default current monitoring (null period)
     *
     * @return array{
     *     periodStart: ?Carbon,
     *     periodEnd: ?Carbon,
     *     periodLabel: ?string,
     *     month: ?int,
     *     year: ?int,
     *     dateFrom: ?string,
     *     dateTo: ?string,
     *     dateBasis: string,
     *     isHistorical: bool
     * }
     */
    public function resolveReportingPeriod(
        ?int $month = null,
        ?int $year = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        ?string $dateBasis = 'stay'
    ): array {
        $allowedBases = ['stay', 'check_in'];
        $basis = in_array(strtolower(trim((string) $dateBasis)), $allowedBases, true)
            ? strtolower(trim((string) $dateBasis))
            : 'stay';

        $periodStart = null;
        $periodEnd = null;
        $periodLabel = null;
        $isHistorical = false;

        $cleanDateFrom = trim((string) ($dateFrom ?? ''));
        $cleanDateTo = trim((string) ($dateTo ?? ''));

        // 1. Priority 1: Custom Date Range (date_from and/or date_to)
        if ($cleanDateFrom !== '' || $cleanDateTo !== '') {
            try {
                if ($cleanDateFrom !== '' && $cleanDateTo !== '') {
                    $startCandidate = Carbon::parse($cleanDateFrom)->startOfDay();
                    $endCandidate = Carbon::parse($cleanDateTo)->endOfDay();

                    if ($endCandidate->lt($startCandidate)) {
                        // Prevent inverted date range
                        $temp = $startCandidate;
                        $startCandidate = $endCandidate->copy()->startOfDay();
                        $endCandidate = $temp->copy()->endOfDay();
                    }

                    $periodStart = $startCandidate;
                    $periodEnd = $endCandidate;
                    $periodLabel = $periodStart->format('M d, Y') . ' – ' . $periodEnd->format('M d, Y');
                    $isHistorical = true;
                } elseif ($cleanDateFrom !== '') {
                    $periodStart = Carbon::parse($cleanDateFrom)->startOfDay();
                    $periodEnd = $periodStart->copy()->endOfDay();
                    $periodLabel = $periodStart->format('M d, Y');
                    $isHistorical = true;
                } else {
                    $periodEnd = Carbon::parse($cleanDateTo)->endOfDay();
                    $periodStart = Carbon::create(2000, 1, 1)->startOfDay();
                    $periodLabel = 'Up to ' . $periodEnd->format('M d, Y');
                    $isHistorical = true;
                }
            } catch (\Throwable $e) {
                // Fall back if invalid date string passed
                $periodStart = null;
                $periodEnd = null;
                $periodLabel = null;
            }
        }

        // 2. Priority 2: Month / Year selection if custom range was not applied
        $validMonth = is_numeric($month) && (int) $month >= 1 && (int) $month <= 12
            ? (int) $month
            : null;

        $validYear = is_numeric($year) && (int) $year >= 2000 && (int) $year <= 2100
            ? (int) $year
            : null;

        if (!$isHistorical && ($validMonth !== null || $validYear !== null)) {
            $isHistorical = true;
            $periodYear = $validYear ?: now()->year;

            if ($validMonth !== null) {
                $periodStart = Carbon::create($periodYear, $validMonth, 1)->startOfDay();
                $periodEnd = $periodStart->copy()->endOfMonth()->endOfDay();
                $periodLabel = $periodStart->format('F Y');
            } else {
                $periodStart = Carbon::create($periodYear, 1, 1)->startOfDay();
                $periodEnd = Carbon::create($periodYear, 12, 31)->endOfDay();
                $periodLabel = (string) $periodYear;
            }
        }

        return [
            'periodStart' => $periodStart,
            'periodEnd' => $periodEnd,
            'periodLabel' => $periodLabel,
            'month' => $validMonth,
            'year' => $validYear,
            'dateFrom' => $cleanDateFrom !== '' ? $cleanDateFrom : null,
            'dateTo' => $cleanDateTo !== '' ? $cleanDateTo : null,
            'dateBasis' => $basis,
            'isHistorical' => $isHistorical,
        ];
    }

    /**
     * Build the single canonical base query for student monitoring and distribution reports.
     *
     * @param array<string, mixed> $filters
     */
    public function buildBaseQuery(array $filters = []): Builder
    {
        $query = Booking::query()
            ->whereHas('student', function (Builder $studentQuery) {
                $studentQuery->where('role', 'student');
            })
            ->with([
                'student:id,student_id,name,full_name,email,contact_number,college,program,major,gender',
                'room:id,property_id,room_number',
                'room.property:id,name,address,landlord_id',
                'room.property.landlord:id,full_name,name,email,contact_number',
            ]);

        // 1. Property / Boarding House Filter
        $propertyId = $filters['property_id'] ?? $filters['boarding_house'] ?? null;
        $this->applyPropertyFilter($query, $propertyId);

        // 2. Academic Filters (College & Program)
        $college = $filters['college'] ?? null;
        $program = $filters['program'] ?? null;
        $this->applyAcademicFilters($query, $college, $program);

        // 3. Reporting Period Filter with explicit Date Basis
        $periodStart = $filters['periodStart'] ?? null;
        $periodEnd = $filters['periodEnd'] ?? null;
        $dateBasis = $filters['dateBasis'] ?? $filters['date_basis'] ?? 'stay';

        if ($periodStart instanceof Carbon && $periodEnd instanceof Carbon) {
            $this->applyPeriodFilter($query, $periodStart, $periodEnd, (string) $dateBasis);
        }

        // 4. Search Filter
        $search = $filters['search'] ?? null;
        $this->applySearch($query, $search);

        return $query;
    }

    /**
     * Filter by property / boarding house ID.
     */
    public function applyPropertyFilter(Builder $query, mixed $propertyId): Builder
    {
        $numericId = is_numeric($propertyId) ? (int) $propertyId : 0;

        if ($numericId > 0) {
            $query->whereHas('room.property', function (Builder $propertyQuery) use ($numericId) {
                $propertyQuery->whereKey($numericId);
            });
        }

        return $query;
    }

    /**
     * Filter by student college and program.
     */
    public function applyAcademicFilters(Builder $query, ?string $college, ?string $program): Builder
    {
        $college = trim((string) ($college ?? ''));
        $program = trim((string) ($program ?? ''));

        if ($college !== '') {
            $query->whereHas('student', function (Builder $studentQuery) use ($college) {
                if (strcasecmp($college, 'Not specified') === 0 || strcasecmp($college, 'unspecified') === 0) {
                    $studentQuery->where(function (Builder $sq) {
                        $sq->whereNull('college')
                            ->orWhere('college', '');
                    });
                } else {
                    $studentQuery->where('college', $college);
                }
            });
        }

        if ($program !== '') {
            $query->whereHas('student', function (Builder $studentQuery) use ($program) {
                if (strcasecmp($program, 'Not specified') === 0 || strcasecmp($program, 'unspecified') === 0) {
                    $studentQuery->where(function (Builder $sq) {
                        $sq->whereNull('program')
                            ->orWhere('program', '');
                    });
                } else {
                    $studentQuery->where('program', $program);
                }
            });
        }

        return $query;
    }

    /**
     * Apply date filtering based on the selected reporting mode:
     *
     * MODE 1: 'stay' (Stayed During Period)
     *   Mathematical overlap condition:
     *   check_in <= periodEnd AND (check_out IS NULL OR check_out >= periodStart)
     *   (Plus early-leave cancelled stays where check_out > check_in)
     *
     * MODE 2: 'check_in' (Started Boarding During Period)
     *   Started condition:
     *   check_in >= periodStart AND check_in <= periodEnd
     */
    public function applyPeriodFilter(
        Builder $query,
        Carbon $periodStart,
        Carbon $periodEnd,
        string $dateBasis = 'stay'
    ): Builder {
        $startStr = $periodStart->toDateString();
        $endStr = $periodEnd->toDateString();
        $table = $query->getModel()->getTable();
        $isCheckInBasis = (strtolower(trim($dateBasis)) === 'check_in');

        if ($isCheckInBasis) {
            return $query->where(function (Builder $q) use ($startStr, $endStr, $table) {
                $q->whereDate("{$table}.check_in", '>=', $startStr)
                    ->whereDate("{$table}.check_in", '<=', $endStr)
                    ->where(function (Builder $validStatus) use ($table) {
                        $validStatus->whereIn("{$table}.status", ['approved', 'pending'])
                            ->orWhere(function (Builder $earlyLeave) use ($table) {
                                $earlyLeave->where("{$table}.status", 'cancelled')
                                    ->whereNotNull("{$table}.check_out")
                                    ->whereColumn("{$table}.check_out", '>', "{$table}.check_in");
                            });
                    });
            });
        }

        // Default 'stay' overlap mode
        return $query->where(function (Builder $outer) use ($startStr, $endStr, $table) {
            // Case 1: Approved stays overlapping the reporting period
            $outer->where(function (Builder $approved) use ($startStr, $endStr, $table) {
                $approved->where("{$table}.status", 'approved')
                    ->whereDate("{$table}.check_in", '<=', $endStr)
                    ->where(function (Builder $dates) use ($startStr, $table) {
                        $dates->whereNull("{$table}.check_out")
                            ->orWhereDate("{$table}.check_out", '>=', $startStr);
                    });
            })
            // Case 2: Early-leave cancelled stays that had physical occupancy (check_out > check_in)
            ->orWhere(function (Builder $earlyLeave) use ($startStr, $endStr, $table) {
                $earlyLeave->where("{$table}.status", 'cancelled')
                    ->whereNotNull("{$table}.check_in")
                    ->whereNotNull("{$table}.check_out")
                    ->whereColumn("{$table}.check_out", '>', "{$table}.check_in")
                    ->whereDate("{$table}.check_in", '<=', $endStr)
                    ->whereDate("{$table}.check_out", '>=', $startStr);
            })
            // Case 3: Pending stays with requested date overlap
            ->orWhere(function (Builder $pending) use ($startStr, $endStr, $table) {
                $pending->where("{$table}.status", 'pending')
                    ->where(function (Builder $pDates) use ($startStr, $endStr, $table) {
                        $pDates->whereNull("{$table}.check_in")
                            ->orWhere(function (Builder $overlap) use ($startStr, $endStr, $table) {
                                $overlap->whereDate("{$table}.check_in", '<=', $endStr)
                                    ->where(function (Builder $out) use ($startStr, $table) {
                                        $out->whereNull("{$table}.check_out")
                                            ->orWhereDate("{$table}.check_out", '>=', $startStr);
                                    });
                            });
                    });
            });
        });
    }

    /**
     * Filter by search term across student identity, boarding house name, and room number.
     */
    public function applySearch(Builder $query, ?string $search): Builder
    {
        $search = trim((string) ($search ?? ''));

        if ($search === '') {
            return $query;
        }

        $like = '%' . $search . '%';

        return $query->where(function (Builder $searchQuery) use ($search, $like) {
            if (ctype_digit($search)) {
                $numericVal = (int) $search;
                $searchQuery->orWhere('bookings.id', $numericVal)
                    ->orWhere('bookings.student_id', $numericVal);
            }

            $searchQuery->orWhereHas('student', function (Builder $studentQuery) use ($like) {
                $studentQuery->where('full_name', 'like', $like)
                    ->orWhere('name', 'like', $like)
                    ->orWhere('student_id', 'like', $like)
                    ->orWhere('email', 'like', $like)
                    ->orWhere('contact_number', 'like', $like)
                    ->orWhere('college', 'like', $like)
                    ->orWhere('program', 'like', $like)
                    ->orWhere('major', 'like', $like);
            });

            $searchQuery->orWhereHas('room', function (Builder $roomQuery) use ($like) {
                $roomQuery->where('room_number', 'like', $like);
            });

            $searchQuery->orWhereHas('room.property', function (Builder $propertyQuery) use ($like) {
                $propertyQuery->where('name', 'like', $like)
                    ->orWhere('address', 'like', $like);
            });

            $searchQuery->orWhereHas('room.property.landlord', function (Builder $landlordQuery) use ($like) {
                $landlordQuery->where('full_name', 'like', $like)
                    ->orWhere('name', 'like', $like)
                    ->orWhere('email', 'like', $like);
            });
        });
    }

    /**
     * Apply status filter to base query.
     */
    public function applyStatusFilter(
        Builder $query,
        string $status,
        ?Carbon $asOf = null,
        ?Carbon $periodStart = null,
        ?Carbon $periodEnd = null,
        string $dateBasis = 'stay'
    ): Builder {
        $status = strtolower(trim($status));
        if ($status === '' || $status === 'all') {
            return $query;
        }

        $asOfDate = ($asOf ?: now())->copy()->startOfDay()->toDateString();
        $table = $query->getModel()->getTable();

        $periodStartStr = $periodStart ? $periodStart->toDateString() : $asOfDate;
        $periodEndStr = $periodEnd ? $periodEnd->toDateString() : $asOfDate;
        $isCheckInBasis = (strtolower(trim($dateBasis)) === 'check_in');

        return match ($status) {
            'active' => $query->where(function (Builder $activeQuery) use ($table, $asOfDate, $periodStartStr, $periodEndStr, $periodStart, $isCheckInBasis) {
                if ($periodStart) {
                    if ($isCheckInBasis) {
                        $activeQuery->whereDate("{$table}.check_in", '>=', $periodStartStr)
                            ->whereDate("{$table}.check_in", '<=', $periodEndStr)
                            ->where("{$table}.status", 'approved');
                    } else {
                        $activeQuery->where(function (Builder $approved) use ($table, $periodStartStr, $periodEndStr) {
                            $approved->where("{$table}.status", 'approved')
                                ->whereDate("{$table}.check_in", '<=', $periodEndStr)
                                ->where(function (Builder $dates) use ($table, $periodStartStr) {
                                    $dates->whereNull("{$table}.check_out")
                                        ->orWhereDate("{$table}.check_out", '>=', $periodStartStr);
                                });
                        })->orWhere(function (Builder $earlyLeave) use ($table, $periodStartStr, $periodEndStr) {
                            $earlyLeave->where("{$table}.status", 'cancelled')
                                ->whereNotNull("{$table}.check_in")
                                ->whereNotNull("{$table}.check_out")
                                ->whereColumn("{$table}.check_out", '>', "{$table}.check_in")
                                ->whereDate("{$table}.check_in", '<=', $periodEndStr)
                                ->whereDate("{$table}.check_out", '>=', $periodStartStr);
                        });
                    }
                } else {
                    $activeQuery->where("{$table}.status", 'approved')
                        ->whereDate("{$table}.check_in", '<=', $asOfDate)
                        ->where(function (Builder $dates) use ($table, $asOfDate) {
                            $dates->whereNull("{$table}.check_out")
                                ->orWhereDate("{$table}.check_out", '>', $asOfDate);
                        });
                }
            }),

            'checked_out' => $query->where(function (Builder $checkedOutQuery) use ($table, $asOfDate, $periodStart, $periodEndStr) {
                if ($periodStart) {
                    $checkedOutQuery->where("{$table}.status", 'approved')
                        ->whereNotNull("{$table}.check_out")
                        ->whereDate("{$table}.check_out", '<=', $periodEndStr);
                } else {
                    $checkedOutQuery->where("{$table}.status", 'approved')
                        ->whereNotNull("{$table}.check_out")
                        ->whereDate("{$table}.check_out", '<=', $asOfDate);
                }
            }),

            'pending' => $query->where(function (Builder $pendingQuery) use ($table, $asOfDate, $periodStart, $periodEndStr) {
                $pendingQuery->where("{$table}.status", 'pending')
                    ->orWhere(function (Builder $future) use ($table, $asOfDate, $periodStart, $periodEndStr) {
                        $future->where("{$table}.status", 'approved');
                        if ($periodStart) {
                            $future->whereDate("{$table}.check_in", '>', $periodEndStr);
                        } else {
                            $future->where(function (Builder $dates) use ($table, $asOfDate) {
                                $dates->whereNull("{$table}.check_in")
                                    ->orWhereDate("{$table}.check_in", '>', $asOfDate);
                            });
                        }
                    });
            }),

            'cancelled' => $query->where(function (Builder $cancelledQuery) use ($table) {
                $cancelledQuery->whereIn("{$table}.status", ['cancelled', 'rejected']);
            }),

            default => $query,
        };
    }

    /**
     * Compute summary metrics for KPI dashboard cards.
     *
     * All headcount metrics strictly utilize de-duplicated COUNT(DISTINCT bookings.student_id).
     *
     * @return array<string, int>
     */
    public function getSummaryMetrics(
        Builder $baseQuery,
        ?Carbon $asOf = null,
        ?Carbon $periodStart = null,
        ?Carbon $periodEnd = null,
        string $dateBasis = 'stay'
    ): array {
        $totalRecords = (clone $baseQuery)->count();
        $uniqueStudents = (clone $baseQuery)->distinct('bookings.student_id')->count('bookings.student_id');

        $activeQuery = (clone $baseQuery);
        $this->applyStatusFilter($activeQuery, 'active', $asOf, $periodStart, $periodEnd, $dateBasis);
        $activeBoardings = (clone $activeQuery)->count();
        $activeTenants = (clone $activeQuery)->distinct('bookings.student_id')->count('bookings.student_id');

        $checkedOutQuery = (clone $baseQuery);
        $this->applyStatusFilter($checkedOutQuery, 'checked_out', $asOf, $periodStart, $periodEnd, $dateBasis);
        $checkedOutBoardings = (clone $checkedOutQuery)->count();
        $checkedOutTenants = (clone $checkedOutQuery)->distinct('bookings.student_id')->count('bookings.student_id');

        $pendingQuery = (clone $baseQuery);
        $this->applyStatusFilter($pendingQuery, 'pending', $asOf, $periodStart, $periodEnd, $dateBasis);
        $pendingBoardings = (clone $pendingQuery)->count();

        $cancelledQuery = (clone $baseQuery);
        $this->applyStatusFilter($cancelledQuery, 'cancelled', $asOf, $periodStart, $periodEnd, $dateBasis);
        $cancelledBoardings = (clone $cancelledQuery)->count();

        $activeRooms = (clone $activeQuery)->distinct('bookings.room_id')->count('bookings.room_id');

        $activeProperties = (clone $activeQuery)
            ->join('rooms as metric_rooms', 'metric_rooms.id', '=', 'bookings.room_id')
            ->distinct('metric_rooms.property_id')
            ->count('metric_rooms.property_id');

        return [
            'total_records' => $totalRecords,
            'unique_students' => $uniqueStudents,
            'active_boardings' => $activeBoardings,
            'active_tenants' => $activeTenants,
            'checked_out_boardings' => $checkedOutBoardings,
            'checked_out_tenants' => $checkedOutTenants,
            'pending_boardings' => $pendingBoardings,
            'cancelled_boardings' => $cancelledBoardings,
            'active_rooms' => $activeRooms,
            'active_properties' => $activeProperties,
        ];
    }

    /**
     * Compute College distribution summaries using de-duplicated student headcounts.
     */
    public function getCollegeDistribution(Builder $baseQuery): Collection
    {
        $catalog = AcademicCatalogService::getCatalog();
        $collegeCatalog = $catalog['colleges'] ?? [];

        $collegeExpr = "COALESCE(NULLIF(TRIM(users.college), ''), 'Not specified')";

        $rows = (clone $baseQuery)
            ->join('users', 'users.id', '=', 'bookings.student_id')
            ->select(
                DB::raw("{$collegeExpr} as college_code"),
                DB::raw('COUNT(DISTINCT bookings.student_id) as total_students'),
                DB::raw('COUNT(bookings.id) as total_records')
            )
            ->groupByRaw($collegeExpr)
            ->orderByDesc('total_students')
            ->get();

        return $rows->map(function ($row) use ($collegeCatalog) {
            $code = trim((string) $row->college_code);
            $fullName = $collegeCatalog[$code] ?? ($code === 'Not specified' ? 'Not specified' : $code);

            return (object) [
                'college_code' => $code,
                'college_name' => $fullName,
                'total_students' => (int) $row->total_students,
                'total_records' => (int) $row->total_records,
            ];
        })->sortByDesc('total_students')->values();
    }

    /**
     * Compute Program distribution summaries within College using de-duplicated student headcounts.
     */
    public function getProgramDistribution(Builder $baseQuery): Collection
    {
        $catalog = AcademicCatalogService::getCatalog();
        $collegeCatalog = $catalog['colleges'] ?? [];

        $collegeExpr = "COALESCE(NULLIF(TRIM(users.college), ''), 'Not specified')";
        $programExpr = "COALESCE(NULLIF(TRIM(users.program), ''), 'Not specified')";

        $rows = (clone $baseQuery)
            ->join('users', 'users.id', '=', 'bookings.student_id')
            ->select(
                DB::raw("{$collegeExpr} as college_code"),
                DB::raw("{$programExpr} as program_name"),
                DB::raw('COUNT(DISTINCT bookings.student_id) as total_students'),
                DB::raw('COUNT(bookings.id) as total_records')
            )
            ->groupByRaw($collegeExpr)
            ->groupByRaw($programExpr)
            ->orderByDesc('total_students')
            ->get();

        return $rows->map(function ($row) use ($collegeCatalog) {
            $collegeCode = trim((string) $row->college_code);
            $programName = trim((string) $row->program_name);

            if ($collegeCode === 'Not specified' && $programName !== 'Not specified') {
                $inferred = AcademicCatalogService::inferCollegeByProgram($programName);
                if ($inferred) {
                    $collegeCode = $inferred;
                }
            }

            return (object) [
                'college_code' => $collegeCode,
                'college_name' => $collegeCatalog[$collegeCode] ?? ($collegeCode === 'Not specified' ? 'Not specified' : $collegeCode),
                'program_name' => $programName,
                'total_students' => (int) $row->total_students,
                'total_records' => (int) $row->total_records,
            ];
        })->sortByDesc('total_students')->values();
    }

    /**
     * Compute Property / Boarding House distribution using de-duplicated student headcounts.
     */
    public function getPropertyDistribution(Builder $baseQuery): Collection
    {
        return (clone $baseQuery)
            ->join('rooms as prop_rooms', 'prop_rooms.id', '=', 'bookings.room_id')
            ->join('properties as prop_properties', 'prop_properties.id', '=', 'prop_rooms.property_id')
            ->select(
                'prop_properties.id as property_id',
                'prop_properties.name as property_name',
                'prop_properties.address as property_address',
                DB::raw('COUNT(DISTINCT bookings.student_id) as total_students'),
                DB::raw('COUNT(DISTINCT bookings.room_id) as total_rooms'),
                DB::raw('COUNT(bookings.id) as total_records')
            )
            ->groupBy('prop_properties.id', 'prop_properties.name', 'prop_properties.address')
            ->orderByDesc('total_students')
            ->get()
            ->map(function ($row) {
                return (object) [
                    'property_id' => (int) $row->property_id,
                    'property_name' => (string) $row->property_name,
                    'property_address' => (string) ($row->property_address ?? ''),
                    'total_students' => (int) $row->total_students,
                    'total_rooms' => (int) $row->total_rooms,
                    'total_records' => (int) $row->total_records,
                ];
            });
    }

    /**
     * Generate dynamic year options based on stored booking spans and current year.
     */
    public function getAvailableYears(): Collection
    {
        $years = collect([now()->year, now()->year - 1, now()->year - 2, now()->year + 1]);

        Booking::query()
            ->select(['check_in', 'check_out'])
            ->whereNotNull('check_in')
            ->chunk(200, function ($bookings) use (&$years) {
                foreach ($bookings as $booking) {
                    if ($booking->check_in) {
                        $years->push(Carbon::parse($booking->check_in)->year);
                    }
                    if ($booking->check_out) {
                        $years->push(Carbon::parse($booking->check_out)->year);
                    }
                }
            });

        return $years->filter(fn ($y) => $y >= 2000 && $y <= 2100)
            ->unique()
            ->sortDesc()
            ->values();
    }

    /**
     * Get all filter options for UI dropdowns.
     */
    public function getFilterOptions(): array
    {
        $catalog = AcademicCatalogService::getCatalog();
        $catalogColleges = $catalog['colleges'] ?? [];
        $catalogPrograms = $catalog['programs'] ?? [];

        $boardingHouses = Property::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        $existingColleges = User::query()
            ->where('role', 'student')
            ->whereNotNull('college')
            ->where('college', '<>', '')
            ->distinct()
            ->pluck('college');

        $allCollegeCodes = collect(array_keys($catalogColleges))
            ->merge($existingColleges)
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $colleges = $allCollegeCodes->map(function ($code) use ($catalogColleges) {
            return [
                'code' => $code,
                'name' => $catalogColleges[$code] ?? $code,
            ];
        });

        $existingPrograms = User::query()
            ->where('role', 'student')
            ->whereNotNull('program')
            ->where('program', '<>', '')
            ->distinct()
            ->pluck('program');

        $flatCatalogPrograms = collect($catalogPrograms)->flatten()->filter();

        $programs = $flatCatalogPrograms
            ->merge($existingPrograms)
            ->unique()
            ->sort()
            ->values();

        return [
            'boardingHouses' => $boardingHouses,
            'colleges' => $colleges,
            'programs' => $programs,
            'catalogColleges' => $catalogColleges,
            'catalogPrograms' => $catalogPrograms,
            'years' => $this->getAvailableYears(),
            'dateBases' => [
                'stay' => 'Stayed During Period (Occupancy)',
                'check_in' => 'Started Boarding During Period (Check-in Date)',
            ],
            'statuses' => [
                'all' => 'All Statuses',
                'active' => 'Active / Present',
                'checked_out' => 'Checked Out',
                'pending' => 'Pending',
                'cancelled' => 'Cancelled',
            ],
        ];
    }
}
