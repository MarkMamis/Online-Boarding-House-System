<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Property;
use App\Models\Room;
use App\Models\Booking;
use App\Models\Message;
use App\Models\LeaveRequest;
use App\Models\Report;
use App\Models\RoomFeedback;
use Illuminate\Support\Facades\Schema;
use App\Models\LandlordProfile;
use App\Models\LandlordDocument;
use App\Models\TenantOnboarding;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Notifications\SystemNotification;
use App\Services\AcademicCatalogService;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $credentials = [
            'email' => $request->email,
            'password' => $request->password,
        ];

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Invalid credentials'])->withInput($request->only('email'));
        }

        $request->session()->regenerate();

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (Schema::hasColumn('users', 'is_active') && !$user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return back()->withErrors(['email' => 'Your account is deactivated. Please contact the administrator.'])->withInput($request->only('email'));
        }

        if (
            $user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail
            && $user->role !== 'admin'
            && !$user->hasVerifiedEmail()
        ) {
            return redirect()->route('verification.notice');
        }

        return $this->redirectAuthenticatedUser($user);
    }

    public function showRegisterForm()
    {
        return view('auth.register');
    }

    public function showRegisterStudentForm()
    {
        return redirect()->route('register');
    }

    public function showRegisterLandlordForm()
    {
        return redirect()->route('register');
    }

    public function showRegisterAdminForm()
    {
        if (!config('auth.public_admin_registration', false) && (!Auth::check() || Auth::user()->role !== 'admin')) {
            abort(403);
        }

        return view('auth.register_admin');
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'terms_accepted' => 'accepted',
        ], [
            'terms_accepted.accepted' => 'You must accept the Data Privacy Notice and Terms before creating an account.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = User::create([
            'full_name' => $request->full_name,
            'name' => $request->full_name, // for compatibility
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'contact_number' => '',
            'boarding_house_name' => '',
            'role' => 'pending',
            'onboarding_complete' => false,
        ]);

        $user->sendEmailVerificationNotification();

        return redirect()->route('login')->with('success', 'Registration successful. Please verify your email before logging in.');
    }

    public function registerAdmin(Request $request)
    {
        if (!config('auth.public_admin_registration', false) && (!Auth::check() || Auth::user()->role !== 'admin')) {
            abort(403);
        }

        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'contact_number' => 'required|string|max:20',
            'terms_accepted' => 'accepted',
        ], [
            'terms_accepted.accepted' => 'You must accept the Data Privacy Notice and Terms before creating an admin account.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        User::create([
            'full_name' => $request->full_name,
            'name' => $request->full_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'contact_number' => $request->contact_number,
            'boarding_house_name' => 'N/A',
            'role' => 'admin',
            'onboarding_complete' => true,
            'email_verified_at' => now(),
        ]);

        return redirect()->route('login')->with('success', 'Admin account created successfully. You can now sign in.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }

    public function adminDashboard()
    {
        // Aggregate user role counts
        $roleCounts = collect();
        $totalUsers = 0;
        $todayNew = 0;
        $last7DaysNew = 0;
        $recentUsers = collect();
        $growthPct = 0;

        try {
            if (Schema::hasTable('users')) {
                $roleCounts = User::select('role', DB::raw('COUNT(*) as total'))
                    ->groupBy('role')
                    ->pluck('total', 'role');

                $totalUsers = User::count();
                $todayNew = User::whereDate('created_at', Carbon::today())->count();
                $last7DaysNew = User::where('created_at', '>=', Carbon::now()->subDays(7))->count();
                $recentUsers = User::orderBy('created_at', 'desc')->limit(8)->get(['full_name','email','role','created_at']);
                $growthPct = $totalUsers > 0 ? round(($last7DaysNew / $totalUsers) * 100, 1) : 0;
            }
        } catch (\Throwable $e) {
            Log::warning('Dashboard user stats error: ' . $e->getMessage());
        }

        // Basic system status (from configuration)
        $systemStatus = [
            'queue' => (string) config('queue.default', 'sync'),
            'cache' => (string) config('cache.default', 'file'),
            'mail' => (string) config('mail.default', 'log'),
            'version' => app()->version(),
        ];

        // Reports count
        $totalReports = 0;
        $pendingReports = 0;
        try {
            if (Schema::hasTable('reports')) {
                $totalReports = \App\Models\Report::count();
                $pendingReports = \App\Models\Report::where('status', 'pending')->count();
            }
        } catch (\Throwable $e) {
            Log::warning('Dashboard reports error: ' . $e->getMessage());
        }

        // Onboarding statistics
        $totalOnboardings = 0;
        $pendingOnboardings = 0;
        $completedOnboardings = 0;
        $activeOnboardings = 0;
        try {
            if (Schema::hasTable('tenant_onboardings')) {
                $totalOnboardings = \App\Models\TenantOnboarding::count();
                $pendingOnboardings = \App\Models\TenantOnboarding::where('status', 'pending')->count();
                $completedOnboardings = \App\Models\TenantOnboarding::where('status', 'completed')->count();
                $activeOnboardings = \App\Models\TenantOnboarding::whereNotIn('status', ['completed', 'cancelled'])->count();
            }
        } catch (\Throwable $e) {
            Log::warning('Dashboard onboardings error: ' . $e->getMessage());
        }

        // Properties statistics
        $totalProperties = 0;
        $activeProperties = 0;
        $pendingApprovals = 0;
        try {
            if (Schema::hasTable('properties')) {
                $totalProperties = Property::count();
                if (Schema::hasTable('rooms')) {
                    $activeProperties = Property::whereHas('rooms')->count();
                }
                if (Schema::hasColumn('properties', 'approval_status')) {
                    $pendingApprovals = Property::where('approval_status', 'pending')->count();
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Dashboard properties error: ' . $e->getMessage());
        }

        // Permit approval statistics (schema-safe for environments not yet migrated)
        $pendingPermitApprovals = 0;
        $approvedPermitApprovals = 0;
        $rejectedPermitApprovals = 0;
        try {
            if (Schema::hasTable('landlord_profiles') && Schema::hasColumn('landlord_profiles', 'business_permit_status')) {
                $pendingPermitApprovals = LandlordProfile::where('business_permit_status', 'pending')->count();
                $approvedPermitApprovals = LandlordProfile::where('business_permit_status', 'approved')->count();
                $rejectedPermitApprovals = LandlordProfile::where('business_permit_status', 'rejected')->count();
            }
        } catch (\Throwable $e) {
            Log::warning('Dashboard permits error: ' . $e->getMessage());
        }

        // Booking statistics
        $totalBookings = 0;
        $pendingBookings = 0;
        $approvedBookings = 0;
        try {
            if (Schema::hasTable('bookings')) {
                $totalBookings = Booking::count();
                $pendingBookings = Booking::where('status', 'pending')->count();
                $approvedBookings = Booking::where('status', 'approved')->count();
            }
        } catch (\Throwable $e) {
            Log::warning('Dashboard bookings error: ' . $e->getMessage());
        }

        $today = Carbon::today()->toDateString();
        $boardedByBoardingHouse = collect();
        $boardedByAcademic = collect();
        $activeBoardedStudents = 0;

        try {
            if (Schema::hasTable('bookings') && Schema::hasTable('rooms') && Schema::hasTable('properties') && Schema::hasTable('users')) {
                $activeBoardingsBase = DB::table('bookings')
                    ->join('rooms', 'rooms.id', '=', 'bookings.room_id')
                    ->join('properties', 'properties.id', '=', 'rooms.property_id')
                    ->join('users as students', 'students.id', '=', 'bookings.student_id')
                    ->where('bookings.status', 'approved')
                    ->whereDate('bookings.check_in', '<=', $today)
                    ->where(function ($query) use ($today) {
                        $query->whereNull('bookings.check_out')
                            ->orWhereDate('bookings.check_out', '>', $today);
                    });

                $boardedByBoardingHouse = (clone $activeBoardingsBase)
                    ->select(
                        'properties.id',
                        'properties.name',
                        'properties.address',
                        DB::raw('COUNT(DISTINCT bookings.student_id) as total_students')
                    )
                    ->groupBy('properties.id', 'properties.name', 'properties.address')
                    ->orderByDesc('total_students')
                    ->limit(8)
                    ->get();

                $boardedByAcademic = $this->buildBoardedByAcademic($activeBoardingsBase);

                $activeBoardedStudents = (clone $activeBoardingsBase)
                    ->distinct('bookings.student_id')
                    ->count('bookings.student_id');
            }
        } catch (\Throwable $e) {
            Log::warning('Dashboard active boardings error: ' . $e->getMessage());
        }

        // Gender analytics with schema-safe fallback if column does not exist.
        $boarderGenderCounts = [
            'male' => 0,
            'female' => 0,
            'unspecified' => 0,
        ];
        $registeredGenderCounts = [
            'male' => 0,
            'female' => 0,
            'unspecified' => 0,
        ];

        try {
            if (Schema::hasColumn('users', 'gender')) {
                // 1. Registered students gender breakdown
                $regExpression = "LOWER(COALESCE(NULLIF(TRIM(gender), ''), 'unspecified'))";
                $regSubQuery = User::where('role', 'student')
                    ->select([
                        'id',
                        DB::raw($regExpression . ' as gender_key'),
                    ]);

                $registeredBreakdown = DB::query()
                    ->fromSub($regSubQuery, 'registered_student_genders')
                    ->select('gender_key', DB::raw('COUNT(*) as total_students'))
                    ->groupBy('gender_key')
                    ->pluck('total_students', 'gender_key');

                $registeredGenderCounts['male'] = (int) ($registeredBreakdown['male'] ?? 0);
                $registeredGenderCounts['female'] = (int) ($registeredBreakdown['female'] ?? 0);
                $registeredGenderCounts['unspecified'] = (int) ($registeredBreakdown['unspecified'] ?? 0);

                foreach ($registeredBreakdown as $key => $count) {
                    if (!in_array($key, ['male', 'female', 'unspecified'], true)) {
                        $registeredGenderCounts['unspecified'] += (int) $count;
                    }
                }

                // 2. Active boarders gender breakdown
                if (isset($activeBoardingsBase)) {
                    $boarderExpression = "LOWER(COALESCE(NULLIF(TRIM(students.gender), ''), 'unspecified'))";
                    $boarderSubQuery = (clone $activeBoardingsBase)
                        ->select([
                            'bookings.student_id',
                            DB::raw($boarderExpression . ' as gender_key'),
                        ]);

                    $boarderBreakdown = DB::query()
                        ->fromSub($boarderSubQuery, 'active_boarder_genders')
                        ->select('gender_key', DB::raw('COUNT(DISTINCT student_id) as total_students'))
                        ->groupBy('gender_key')
                        ->pluck('total_students', 'gender_key');

                    $boarderGenderCounts['male'] = (int) ($boarderBreakdown['male'] ?? 0);
                    $boarderGenderCounts['female'] = (int) ($boarderBreakdown['female'] ?? 0);
                    $boarderGenderCounts['unspecified'] = (int) ($boarderBreakdown['unspecified'] ?? 0);

                    foreach ($boarderBreakdown as $key => $count) {
                        if (!in_array($key, ['male', 'female', 'unspecified'], true)) {
                            $boarderGenderCounts['unspecified'] += (int) $count;
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Dashboard gender aggregation error: ' . $e->getMessage());
        }

        // Gender counts displayed on dashboard:
        // Use active boarders distribution if active boarders exist, otherwise registered students distribution
        $genderCounts = ($activeBoardedStudents > 0) ? $boarderGenderCounts : $registeredGenderCounts;

        // Map points for all approved landlord properties with coordinates.
        $landlordMapPoints = collect();
        try {
            if (Schema::hasTable('properties') && Schema::hasColumn('properties', 'latitude') && Schema::hasColumn('properties', 'longitude')) {
                $mapQuery = Property::query()
                    ->with('landlord:id,full_name,email')
                    ->whereNotNull('latitude')
                    ->whereNotNull('longitude');

                if (Schema::hasColumn('properties', 'approval_status')) {
                    $mapQuery->where('approval_status', 'approved');
                }

                $landlordMapPoints = $mapQuery->orderBy('name')
                    ->get(['id', 'name', 'address', 'latitude', 'longitude', 'landlord_id'])
                    ->map(function ($property) {
                        return [
                            'id' => $property->id,
                            'name' => $property->name,
                            'address' => $property->address,
                            'latitude' => (float) $property->latitude,
                            'longitude' => (float) $property->longitude,
                            'landlord_name' => $property->landlord?->full_name,
                            'landlord_email' => $property->landlord?->email,
                        ];
                    })
                    ->values();
            }
        } catch (\Throwable $e) {
            Log::warning('Dashboard map points error: ' . $e->getMessage());
        }

        // Check for pending migrations
        $hasPendingMigrations = false;
        try {
            $migrator = app('migrator');
            $files = $migrator->getMigrationFiles(database_path('migrations'));
            $ran = $migrator->getRepository()->getRan();
            $pending = array_diff(array_keys($files), $ran);
            $hasPendingMigrations = count($pending) > 0;
        } catch (\Throwable $e) {
            $hasPendingMigrations = true;
        }

        // Self-heal stale route cache on Hostinger shared hosting if missing admin.analytics.index
        $cachedRoutes = base_path('bootstrap/cache/routes-v7.php');
        if (file_exists($cachedRoutes) && !\Illuminate\Support\Facades\Route::has('admin.analytics.index')) {
            @unlink($cachedRoutes);
        }

        return view('admin.dashboard', compact(
            'roleCounts', 'totalUsers', 'todayNew', 'last7DaysNew', 'growthPct', 'recentUsers', 'systemStatus', 'totalReports', 'pendingReports',
            'totalOnboardings', 'pendingOnboardings', 'completedOnboardings', 'activeOnboardings', 'totalProperties', 'activeProperties',
            'pendingApprovals', 'totalBookings', 'pendingBookings', 'approvedBookings',
            'pendingPermitApprovals', 'approvedPermitApprovals', 'rejectedPermitApprovals',
            'activeBoardedStudents', 'boardedByBoardingHouse', 'boardedByAcademic', 'genderCounts', 'registeredGenderCounts', 'boarderGenderCounts', 'landlordMapPoints',
            'hasPendingMigrations'
        ));
    }

    private function registrationAcademicCatalog(): array
    {
        return AcademicCatalogService::getCatalog();
    }

    private function redirectAuthenticatedUser(User $user)
    {
        if ($user->role === 'admin') {
            return redirect('/admin/dashboard');
        }

        if ($user->hasPendingRole()) {
            return redirect()->route('onboarding.role.show');
        }

        if ($user->role === 'landlord') {
            $setupSnapshot = $this->getLandlordSetupSnapshot($user);
            if (!($user->onboarding_complete ?? false) || !($setupSnapshot['setup_submitted'] ?? false)) {
                return redirect()->route('landlord.setup.show');
            }

            return redirect('/landlord/dashboard');
        }

        if ($user->role === 'student') {
            if (!($user->onboarding_complete ?? false) || !$user->isStudentSetupComplete()) {
                return redirect()->route('student.setup.show');
            }

            return redirect('/student/dashboard');
        }

        return redirect()->route('onboarding.role.show');
    }

    private function buildBoardedByAcademic($activeBoardingsBase)
    {
        try {
            $academicCatalog = $this->registrationAcademicCatalog();
            $collegeCatalog = $academicCatalog['colleges'] ?? [];
            $programCatalog = $academicCatalog['programs'] ?? [];

            $programCollegeLookup = [];
            foreach ($programCatalog as $collegeCode => $programs) {
                foreach ((array) $programs as $programName) {
                    $programCollegeLookup[(string) $programName] = (string) $collegeCode;
                }
            }

            $hasCollege = Schema::hasTable('users') && Schema::hasColumn('users', 'college');
            $hasProgram = Schema::hasTable('users') && Schema::hasColumn('users', 'program');

            $collegeExpression = $hasCollege ? "COALESCE(NULLIF(TRIM(students.college), ''), 'Not specified')" : "'Not specified'";
            $programExpression = $hasProgram ? "COALESCE(NULLIF(TRIM(students.program), ''), 'Not specified')" : "'Not specified'";

            $subQuery = (clone $activeBoardingsBase)
                ->select([
                    'bookings.student_id',
                    DB::raw($collegeExpression . ' as college_code'),
                    DB::raw($programExpression . ' as program_name'),
                ]);

            $academicRows = DB::query()
                ->fromSub($subQuery, 'normalized_students')
                ->select([
                    'college_code',
                    'program_name',
                    DB::raw('COUNT(DISTINCT student_id) as total_students'),
                ])
                ->groupBy('college_code', 'program_name')
                ->orderByDesc('total_students')
                ->get();

            return $academicRows
                ->map(function ($row) use ($programCollegeLookup) {
                    $collegeCode = trim((string) $row->college_code);
                    $programName = trim((string) $row->program_name);

                    if ($collegeCode === '' || strcasecmp($collegeCode, 'Not specified') === 0) {
                        $collegeCode = $programCollegeLookup[$programName] ?? 'Not specified';
                    }

                    if ($programName === '') {
                        $programName = 'Not specified';
                    }

                    return [
                        'college_code' => $collegeCode,
                        'program_name' => $programName,
                        'total_students' => (int) $row->total_students,
                    ];
                })
                ->groupBy('college_code')
                ->map(function ($rows, $collegeCode) use ($collegeCatalog) {
                    $programs = collect($rows)
                        ->groupBy('program_name')
                        ->map(function ($programRows, $programName) {
                            return [
                                'name' => $programName,
                                'total_students' => collect($programRows)->sum('total_students'),
                            ];
                        })
                        ->sortByDesc('total_students')
                        ->values();

                    return (object) [
                        'college_code' => $collegeCode,
                        'college_name' => $collegeCatalog[$collegeCode] ?? ($collegeCode === 'Not specified' ? 'Not specified' : $collegeCode),
                        'total_students' => (int) $programs->sum('total_students'),
                        'programs' => $programs,
                    ];
                })
                ->sortByDesc('total_students')
                ->values();
        } catch (\Throwable $e) {
            Log::warning('buildBoardedByAcademic error: ' . $e->getMessage());
            return collect();
        }
    }

    public function runMigrationsWeb(Request $request)
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        try {
            \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
            $cachedRoutes = base_path('bootstrap/cache/routes-v7.php');
            if (file_exists($cachedRoutes)) {
                @unlink($cachedRoutes);
            }
            \Illuminate\Support\Facades\Artisan::call('route:clear');
            \Illuminate\Support\Facades\Artisan::call('view:clear');
            \Illuminate\Support\Facades\Artisan::call('config:clear');
            $output = \Illuminate\Support\Facades\Artisan::output();
            return back()->with('success', 'Database migrations and cache clearance completed successfully! Output: ' . $output);
        } catch (\Throwable $e) {
            return back()->with('error', 'Migration failed: ' . $e->getMessage());
        }
    }

    public function adminDashboardStats(Request $request)
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            abort(403);
        }

        $days = (int) $request->query('days', 30);
        if ($days < 7) {
            $days = 7;
        }
        if ($days > 90) {
            $days = 90;
        }

        $start = Carbon::today()->subDays($days - 1);
        $end = Carbon::today();

        $labels = [];
        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            $labels[] = $d->format('Y-m-d');
        }

        $userCounts = User::query()
            ->select(DB::raw('DATE(created_at) as d'), DB::raw('COUNT(*) as c'))
            ->whereDate('created_at', '>=', $start)
            ->whereDate('created_at', '<=', $end)
            ->groupBy(DB::raw('DATE(created_at)'))
            ->pluck('c', 'd');

        $registrations = array_map(function ($label) use ($userCounts) {
            return (int) ($userCounts[$label] ?? 0);
        }, $labels);

        $approvedByDate = Property::query()
            ->select(DB::raw('DATE(approved_at) as d'), DB::raw('COUNT(*) as c'))
            ->whereNotNull('approved_at')
            ->whereDate('approved_at', '>=', $start)
            ->whereDate('approved_at', '<=', $end)
            ->groupBy(DB::raw('DATE(approved_at)'))
            ->pluck('c', 'd');

        $rejectedByDate = Property::query()
            ->select(DB::raw('DATE(rejected_at) as d'), DB::raw('COUNT(*) as c'))
            ->whereNotNull('rejected_at')
            ->whereDate('rejected_at', '>=', $start)
            ->whereDate('rejected_at', '<=', $end)
            ->groupBy(DB::raw('DATE(rejected_at)'))
            ->pluck('c', 'd');

        $approvalsApproved = array_map(function ($label) use ($approvedByDate) {
            return (int) ($approvedByDate[$label] ?? 0);
        }, $labels);
        $approvalsRejected = array_map(function ($label) use ($rejectedByDate) {
            return (int) ($rejectedByDate[$label] ?? 0);
        }, $labels);

        $bookingCounts = Booking::query()
            ->select('status', DB::raw('COUNT(*) as c'))
            ->groupBy('status')
            ->pluck('c', 'status');

        $bookingStatuses = ['pending', 'approved', 'rejected', 'cancelled'];
        $bookingStatus = [];
        foreach ($bookingStatuses as $status) {
            $bookingStatus[$status] = (int) ($bookingCounts[$status] ?? 0);
        }

        $roomCounts = Room::query()
            ->select('status', DB::raw('COUNT(*) as c'))
            ->groupBy('status')
            ->pluck('c', 'status');

        $roomStatuses = ['available', 'occupied', 'maintenance'];
        $roomStatus = [];
        foreach ($roomStatuses as $status) {
            $roomStatus[$status] = (int) ($roomCounts[$status] ?? 0);
        }

        $today = Carbon::today()->toDateString();
        $activeBoardingsBase = DB::table('bookings')
            ->join('rooms', 'rooms.id', '=', 'bookings.room_id')
            ->join('properties', 'properties.id', '=', 'rooms.property_id')
            ->join('users as students', 'students.id', '=', 'bookings.student_id')
            ->where('bookings.status', 'approved')
            ->whereDate('bookings.check_in', '<=', $today)
            ->where(function ($query) use ($today) {
                $query->whereNull('bookings.check_out')
                    ->orWhereDate('bookings.check_out', '>', $today);
            });

        $boardedByAcademic = $this->buildBoardedByAcademic($activeBoardingsBase)
            ->map(function ($college) {
                return [
                    'college_code' => (string) ($college->college_code ?? 'Not specified'),
                    'college_name' => (string) ($college->college_name ?? 'Not specified'),
                    'total_students' => (int) ($college->total_students ?? 0),
                    'programs' => collect($college->programs ?? [])
                        ->map(function ($program) {
                            return [
                                'name' => (string) ($program['name'] ?? 'Not specified'),
                                'total_students' => (int) ($program['total_students'] ?? 0),
                            ];
                        })
                        ->values(),
                ];
            })
            ->values();

        return response()->json([
            'range' => [
                'days' => $days,
                'start' => $start->format('Y-m-d'),
                'end' => $end->format('Y-m-d'),
            ],
            'labels' => $labels,
            'registrations' => $registrations,
            'approvals' => [
                'approved' => $approvalsApproved,
                'rejected' => $approvalsRejected,
            ],
            'bookingStatus' => $bookingStatus,
            'roomStatus' => $roomStatus,
            'boardedByAcademic' => $boardedByAcademic,
        ]);
    }

    public function adminUsers(Request $request)
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            abort(403);
        }

        $search = trim((string) $request->query('search', ''));
        $roleFilter = strtolower((string) $request->query('role', 'all'));

        $users = User::with('landlordProfile')
            ->whereIn('role', ['student', 'landlord'])
            ->orderBy('created_at', 'desc')
            ->when($roleFilter === 'student' || $roleFilter === 'landlord', function ($query) use ($roleFilter) {
                $query->where('role', $roleFilter);
            })
            ->when($search !== '', function ($query) use ($search) {
                $like = '%' . $search . '%';
                $query->where(function ($inner) use ($like, $search) {
                    $inner->orWhere('full_name', 'like', $like)
                        ->orWhere('name', 'like', $like);
                });
            })
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', compact('users', 'search', 'roleFilter'));
    }

    public function adminUsersByRole(Request $request)
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            abort(403);
        }

        $role = $request->route()->getName() === 'admin.users.students' ? 'student' :
               ($request->route()->getName() === 'admin.users.landlords' ? 'landlord' : 'admin');

         $selectedNameSearch = trim((string) $request->query('search', ''));
        $selectedCollege = trim((string) $request->query('college', ''));
        $selectedProgram = trim((string) $request->query('program', ''));
        $selectedMajor = trim((string) $request->query('major', ''));

        $collegeOptions = [];
        $programOptions = [];
        $majorOptions = [];
        $catalogProgramsByCollege = [];
        $catalogMajorsByProgram = [];

        if ($role === 'student') {
            $catalog = AcademicCatalogService::getCatalog();
            $collegeOptions = is_array($catalog['colleges'] ?? null) ? $catalog['colleges'] : [];
            $catalogProgramsByCollege = is_array($catalog['programs'] ?? null) ? $catalog['programs'] : [];
            $catalogMajorsByProgram = is_array($catalog['majors'] ?? null) ? $catalog['majors'] : [];

            $programOptions = $selectedCollege !== ''
                ? AcademicCatalogService::programsForCollege($selectedCollege)
                : [];

            if ($selectedCollege !== '' && $selectedProgram === '' && count($programOptions) === 1) {
                $selectedProgram = (string) $programOptions[0];
            }

            $majorOptions = $selectedProgram !== ''
                ? AcademicCatalogService::majorsForProgram($selectedProgram)
                : [];

            if ($selectedProgram !== '' && $selectedMajor === '' && count($majorOptions) === 1) {
                $selectedMajor = (string) $majorOptions[0];
            }
        }

        $usersQuery = User::with('landlordProfile')
            ->where('role', $role)
            ->orderBy('created_at', 'desc');

        if ($role === 'student') {
            if ($selectedNameSearch !== '') {
                $like = '%' . $selectedNameSearch . '%';
                $usersQuery->where(function ($query) use ($like) {
                    $query->where('full_name', 'like', $like)
                        ->orWhere('name', 'like', $like);
                });
            }

            if ($selectedCollege !== '') {
                $usersQuery->where('college', $selectedCollege);
            }

            if ($selectedProgram !== '') {
                $usersQuery->where('program', $selectedProgram);
            }

            if ($selectedMajor !== '') {
                $usersQuery->where('major', $selectedMajor);
            }
        }

        $users = $usersQuery->paginate(20);

        if ($role === 'student') {
            $users->appends([
                'search' => $selectedNameSearch,
                'college' => $selectedCollege,
                'program' => $selectedProgram,
                'major' => $selectedMajor,
            ]);
        }

        // Add tenant count for landlords
        if ($role === 'landlord') {
            $users->getCollection()->transform(function ($user) {
                // Count approved bookings (current tenants) for this landlord
                $user->current_tenants = \App\Models\Booking::where('status', 'approved')
                    ->whereHas('room.property', function ($q) use ($user) {
                        $q->where('landlord_id', $user->id);
                    })
                    ->where('check_in', '<=', now()->toDateString())
                    ->where('check_out', '>', now()->toDateString())
                    ->count();

                // Count total rooms owned by this landlord
                $user->total_rooms = \App\Models\Room::whereHas('property', function ($q) use ($user) {
                    $q->where('landlord_id', $user->id);
                })->count();

                // Count occupied rooms
                $user->occupied_rooms = \App\Models\Room::whereHas('property', function ($q) use ($user) {
                    $q->where('landlord_id', $user->id);
                })
                ->whereHas('bookings', function ($q) {
                    $q->where('status', 'approved')
                      ->where('check_in', '<=', now()->toDateString())
                      ->where('check_out', '>', now()->toDateString());
                })->count();

                // Count tenants in onboarding process
                $user->onboarding_tenants = \App\Models\TenantOnboarding::whereHas('booking.room.property', function ($q) use ($user) {
                    $q->where('landlord_id', $user->id);
                })
                ->where('status', '!=', 'completed')
                ->count();

                return $user;
            });
        }

        $roleTitle = ucfirst($role) . 's';

        return view('admin.users.by_role', compact(
            'users',
            'role',
            'roleTitle',
            'selectedNameSearch',
            'selectedCollege',
            'selectedProgram',
            'selectedMajor',
            'collegeOptions',
            'programOptions',
            'majorOptions',
            'catalogProgramsByCollege',
            'catalogMajorsByProgram'
        ));
    }

    public function adminLandlordDetails(User $user)
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            abort(403);
        }

        if ($user->role !== 'landlord') {
            abort(404);
        }

        $user->loadMissing('landlordProfile');

        $landlordDocuments = $user->landlordDocuments()
            ->orderByDesc('is_current')
            ->orderByDesc('submitted_at')
            ->orderByDesc('id')
            ->get();
        $currentLandlordDocuments = $landlordDocuments
            ->where('is_current', true)
            ->keyBy('document_type');
        $documentHistory = $landlordDocuments
            ->where('is_current', false)
            ->groupBy('document_type');

        // Get landlord's properties with room and tenant counts
        $properties = \App\Models\Property::where('landlord_id', $user->id)
            ->with(['rooms' => function($q) {
                $q->with(['bookings' => function($bq) {
                    $bq->where('status', 'approved')
                       ->where('check_in', '<=', now()->toDateString())
                       ->where('check_out', '>', now()->toDateString())
                       ->with('student');
                }]);
            }])
            ->withCount([
                'rooms as total_rooms',
                'rooms as available_rooms' => function($q) {
                    $q->where('status', 'available')->where('slots_available', '>', 0);
                },
                'rooms as occupied_rooms' => function($q) {
                    $q->whereHas('bookings', function($bq) {
                        $bq->where('status', 'approved')
                           ->where('check_in', '<=', now()->toDateString())
                           ->where('check_out', '>', now()->toDateString());
                    });
                }
            ])
            ->get();

        // Calculate tenant statistics
        $totalTenants = 0;
        $properties->each(function($property) use (&$totalTenants) {
            $property->current_tenants = $property->rooms->sum(function($room) {
                return $room->bookings->count();
            });
            $totalTenants += $property->current_tenants;
        });

        $totalProperties = $properties->count();
        $totalRooms = $properties->sum('total_rooms');
        $occupiedRooms = $properties->sum('occupied_rooms');
        $occupancyRate = $totalRooms > 0 ? round(($occupiedRooms / $totalRooms) * 100, 1) : 0;

        // Calculate onboarding statistics
        $onboardingStats = TenantOnboarding::whereHas('booking.room.property', function ($q) use ($user) {
            $q->where('landlord_id', $user->id);
        })->selectRaw('status, COUNT(*) as count')
          ->groupBy('status')
          ->pluck('count', 'status');

        $pendingOnboarding = $onboardingStats->get('pending', 0);
        $documentsUploaded = $onboardingStats->get('documents_uploaded', 0);
        $contractSigned = $onboardingStats->get('contract_signed', 0);
        $depositPaid = $onboardingStats->get('deposit_paid', 0);
        $completedOnboarding = $onboardingStats->get('completed', 0);
        $totalOnboarding = $pendingOnboarding + $documentsUploaded + $contractSigned + $depositPaid + $completedOnboarding;

        // Get current tenants
        $currentTenants = Booking::whereHas('room.property', function ($q) use ($user) {
            $q->where('landlord_id', $user->id);
        })
        ->where('status', 'approved')
        ->where('check_in', '<=', now()->toDateString())
        ->where('check_out', '>', now()->toDateString())
        ->with(['student', 'room.property'])
        ->get()
        ->map(function ($booking) {
            return [
                'id' => $booking->student->id,
                'name' => $booking->student->full_name,
                'email' => $booking->student->email,
                'contact' => $booking->student->contact_number,
                'property_name' => $booking->room->property->name,
                'room_number' => $booking->room->room_number,
                'check_in' => $booking->check_in->format('M d, Y'),
                'check_out' => $booking->check_out->format('M d, Y'),
                'program' => $booking->student->program,
                'year_level' => $booking->student->year_level,
            ];
        });

        // Resolve landlord compliance summary
        $complianceService = app(\App\Services\LandlordDocumentStatusService::class);
        $profile = $user->landlordProfile;
        $compliance = [
            'business_permit' => $complianceService->resolveDocumentStatus($profile, \App\Services\LandlordDocumentStatusService::DOC_BUSINESS_PERMIT),
            'safety_certificate' => $complianceService->resolveDocumentStatus($profile, \App\Services\LandlordDocumentStatusService::DOC_SAFETY_CERTIFICATE),
        ];
        $compliance['is_compliant'] = ($compliance['business_permit'] === 'approved' && $compliance['safety_certificate'] === 'approved');
        $activeTab = request('tab', 'overview');

        return view('admin.users.landlords.show', compact(
            'user', 'properties', 'totalTenants', 'totalProperties',
            'totalRooms', 'occupiedRooms', 'occupancyRate',
            'pendingOnboarding', 'documentsUploaded', 'contractSigned',
            'depositPaid', 'completedOnboarding', 'totalOnboarding',
            'currentTenants', 'currentLandlordDocuments', 'documentHistory',
            'compliance', 'activeTab'
        ));
    }

    public function adminUpdateLandlord(Request $request, User $user)
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            abort(403);
        }

        if ($user->role !== 'landlord') {
            abort(404);
        }

        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'contact_number' => 'nullable|string|max:20',
            'boarding_house_name' => 'nullable|string|max:255',
        ]);

        $user->update($validated);

        return back()->with('success', 'Landlord profile updated successfully.');
    }

    public function adminToggleLandlordStatus(User $user)
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            abort(403);
        }

        if ($user->role !== 'landlord') {
            abort(404);
        }

        if (!Schema::hasColumn('users', 'is_active')) {
            return back()->with('error', 'Unable to update status: users.is_active column not found.');
        }

        $user->is_active = !$user->is_active;
        $user->save();

        if (!$user->is_active) {
            // Revoke current remember token so next auth checks force re-login.
            $user->setRememberToken(null);
            $user->save();
        }

        $message = $user->is_active
            ? 'Landlord account activated successfully.'
            : 'Landlord account deactivated successfully.';

        return back()->with('success', $message);
    }

    public function adminPermitApprovals(Request $request)
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            abort(403);
        }

        if (!Schema::hasColumn('landlord_profiles', 'business_permit_status')) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Permit approval columns are not available yet. Run migrations first.');
        }

        $statusFilter = strtolower((string) $request->query('status', 'pending'));
        $allowedStatuses = ['pending', 'approved', 'rejected', 'all'];
        if (!in_array($statusFilter, $allowedStatuses, true)) {
            $statusFilter = 'pending';
        }

        $landlords = User::query()
            ->where('role', 'landlord')
            ->whereHas('landlordProfile', function ($query) use ($statusFilter) {
                $query->whereNotNull('business_permit_path');

                if ($statusFilter !== 'all') {
                    $query->where('business_permit_status', $statusFilter);
                }
            })
            ->with('landlordProfile')
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $counts = [
            'pending' => LandlordProfile::where('business_permit_status', 'pending')->count(),
            'approved' => LandlordProfile::where('business_permit_status', 'approved')->count(),
            'rejected' => LandlordProfile::where('business_permit_status', 'rejected')->count(),
        ];

        return view('admin.permits.index', compact('landlords', 'statusFilter', 'counts'));
    }

    public function adminStudentApprovals(Request $request)
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            abort(403);
        }

        if (!Schema::hasColumn('users', 'school_id_verification_status')) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Student verification columns are not available yet. Run migrations first.');
        }

        $statusFilter = $this->normalizeApprovalStatusFilter((string) $request->query('status', 'pending'));
        [$students, $counts] = $this->buildStudentApprovalPayload($statusFilter);

        return view('admin.approvals.students', compact('students', 'statusFilter', 'counts'));
    }

    public function adminLandlordApprovals(Request $request)
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            abort(403);
        }

        $activeTab = strtolower((string) $request->query('tab', ''));
        // If tab is properties (or default when not explicitly asking for permits), redirect to unified properties workspace!
        if ($activeTab === 'properties' || ($activeTab === '' && !$request->has('tab'))) {
            return redirect()->route('admin.properties.index', array_filter([
                'status' => 'pending',
                'page' => $request->query('page'),
            ], fn ($v) => $v !== null && $v !== ''));
        }

        $activeTab = 'permits';
        $statusFilter = $this->normalizeLandlordPermitFilter((string) $request->query('status', 'all'));

        if (!Schema::hasColumn('landlord_profiles', 'business_permit_status')) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Permit approval columns are not available yet. Run migrations first.');
        }

        [$landlords, $permitCounts] = $this->buildPermitApprovalPayload($statusFilter);

        return view('admin.approvals.landlords', compact(
            'activeTab',
            'statusFilter',
            'landlords',
            'permitCounts'
        ));
    }

    public function adminApproveLandlordPermit(User $user)
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            abort(403);
        }

        if (!Schema::hasColumn('landlord_profiles', 'business_permit_status')) {
            return back()->with('error', 'Permit approval columns are not available yet. Run migrations first.');
        }

        if ($user->role !== 'landlord') {
            abort(404);
        }

        $landlordProfile = $user->landlordProfile;
        if (empty($landlordProfile) || empty($landlordProfile->business_permit_path)) {
            return back()->with('error', 'No business permit was uploaded for this landlord.');
        }

        $landlordProfile->update([
            'business_permit_status' => 'approved',
            'business_permit_reviewed_at' => now(),
            'business_permit_reviewed_by' => Auth::id(),
            'business_permit_rejection_reason' => null,
        ]);

        if (Schema::hasTable('landlord_documents')) {
            LandlordDocument::updateOrCreate(
                [
                    'landlord_id' => $user->id,
                    'document_type' => LandlordDocument::TYPE_BUSINESS_PERMIT,
                    'is_current' => true,
                ],
                [
                    'file_path' => $landlordProfile->business_permit_path,
                    'verification_status' => LandlordDocument::STATUS_APPROVED,
                    'approved_by' => Auth::id(),
                    'approved_at' => now(),
                    'rejection_reason' => null,
                    'rejected_at' => null,
                    'submitted_at' => $landlordProfile->created_at ?? now(),
                ]
            );
        }

        $user->notify(new SystemNotification(
            'Business permit approved',
            'Your business permit has been approved. You can now proceed with full landlord operations.',
            route('landlord.dashboard'),
            ['type' => 'permit_approved']
        ));

        return back()->with('success', 'Business permit approved successfully.');
    }

    public function adminRejectLandlordPermit(Request $request, User $user)
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            abort(403);
        }

        if (!Schema::hasColumn('landlord_profiles', 'business_permit_status')) {
            return back()->with('error', 'Permit approval columns are not available yet. Run migrations first.');
        }

        if ($user->role !== 'landlord') {
            abort(404);
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $landlordProfile = $user->landlordProfile;
        if (empty($landlordProfile) || empty($landlordProfile->business_permit_path)) {
            return back()->with('error', 'No business permit was uploaded for this landlord.');
        }

        $landlordProfile->update([
            'business_permit_status' => 'rejected',
            'business_permit_reviewed_at' => now(),
            'business_permit_reviewed_by' => Auth::id(),
            'business_permit_rejection_reason' => $validated['rejection_reason'],
        ]);

        if (Schema::hasTable('landlord_documents')) {
            LandlordDocument::updateOrCreate(
                [
                    'landlord_id' => $user->id,
                    'document_type' => LandlordDocument::TYPE_BUSINESS_PERMIT,
                    'is_current' => true,
                ],
                [
                    'file_path' => $landlordProfile->business_permit_path,
                    'verification_status' => LandlordDocument::STATUS_REJECTED,
                    'rejection_reason' => $validated['rejection_reason'],
                    'rejected_at' => now(),
                    'approved_by' => null,
                    'approved_at' => null,
                    'submitted_at' => $landlordProfile->created_at ?? now(),
                ]
            );
        }

        $user->notify(new SystemNotification(
            'Business permit rejected',
            'Your business permit was rejected. Please review the reason and upload an updated permit.',
            route('landlord.setup.show', ['step' => 'permit']),
            [
                'type' => 'permit_rejected',
                'reason' => $validated['rejection_reason'],
            ]
        ));

        return back()->with('success', 'Business permit rejected and landlord notified.');
    }

    public function adminApproveLandlordSafetyCertificate(User $user)
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            abort(403);
        }

        if (!$this->supportsSafetyCertificateApproval()) {
            return back()->with('error', 'Safety certificate approval columns are not available yet. Run migrations first.');
        }

        if ($user->role !== 'landlord') {
            abort(404);
        }

        $landlordProfile = $user->landlordProfile;
        if (empty($landlordProfile) || empty($landlordProfile->safety_certificate_path)) {
            return back()->with('error', 'No safety certificate was uploaded for this landlord.');
        }

        $landlordProfile->update([
            'safety_certificate_status' => 'approved',
            'safety_certificate_reviewed_at' => now(),
            'safety_certificate_reviewed_by' => Auth::id(),
            'safety_certificate_rejection_reason' => null,
        ]);

        if (Schema::hasTable('landlord_documents')) {
            LandlordDocument::updateOrCreate(
                [
                    'landlord_id' => $user->id,
                    'document_type' => LandlordDocument::TYPE_SAFETY_CERTIFICATE,
                    'is_current' => true,
                ],
                [
                    'file_path' => $landlordProfile->safety_certificate_path,
                    'verification_status' => LandlordDocument::STATUS_APPROVED,
                    'approved_by' => Auth::id(),
                    'approved_at' => now(),
                    'rejection_reason' => null,
                    'rejected_at' => null,
                    'submitted_at' => $landlordProfile->created_at ?? now(),
                ]
            );
        }

        $user->notify(new SystemNotification(
            'Safety certificate approved',
            'Your safety certificate has been approved.',
            route('landlord.dashboard'),
            ['type' => 'safety_certificate_approved']
        ));

        return back()->with('success', 'Safety certificate approved successfully.');
    }

    public function adminRejectLandlordSafetyCertificate(Request $request, User $user)
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            abort(403);
        }

        if (!$this->supportsSafetyCertificateApproval()) {
            return back()->with('error', 'Safety certificate approval columns are not available yet. Run migrations first.');
        }

        if ($user->role !== 'landlord') {
            abort(404);
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $landlordProfile = $user->landlordProfile;
        if (empty($landlordProfile) || empty($landlordProfile->safety_certificate_path)) {
            return back()->with('error', 'No safety certificate was uploaded for this landlord.');
        }

        $landlordProfile->update([
            'safety_certificate_status' => 'rejected',
            'safety_certificate_reviewed_at' => now(),
            'safety_certificate_reviewed_by' => Auth::id(),
            'safety_certificate_rejection_reason' => $validated['rejection_reason'],
        ]);

        if (Schema::hasTable('landlord_documents')) {
            LandlordDocument::updateOrCreate(
                [
                    'landlord_id' => $user->id,
                    'document_type' => LandlordDocument::TYPE_SAFETY_CERTIFICATE,
                    'is_current' => true,
                ],
                [
                    'file_path' => $landlordProfile->safety_certificate_path,
                    'verification_status' => LandlordDocument::STATUS_REJECTED,
                    'rejection_reason' => $validated['rejection_reason'],
                    'rejected_at' => now(),
                    'approved_by' => null,
                    'approved_at' => null,
                    'submitted_at' => $landlordProfile->created_at ?? now(),
                ]
            );
        }

        $user->notify(new SystemNotification(
            'Safety certificate rejected',
            'Your safety certificate was rejected. Please review the reason and upload an updated certificate.',
            route('landlord.setup.show', ['step' => 'permit']),
            [
                'type' => 'safety_certificate_rejected',
                'reason' => $validated['rejection_reason'],
            ]
        ));

        return back()->with('success', 'Safety certificate rejected and landlord notified.');
    }

    public function adminStudentVerifications(Request $request)
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            abort(403);
        }

        if (!Schema::hasColumn('users', 'school_id_verification_status')) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Student verification columns are not available yet. Run migrations first.');
        }

        $statusFilter = strtolower((string) $request->query('status', 'pending'));
        $allowedStatuses = ['pending', 'approved', 'rejected', 'all'];
        if (!in_array($statusFilter, $allowedStatuses, true)) {
            $statusFilter = 'pending';
        }

        $studentsWithVerificationDocs = function ($query) {
            $query->where(function ($docQuery) {
                $docQuery->where(function ($schoolIdQuery) {
                    $schoolIdQuery->whereNotNull('school_id_path')
                        ->where('school_id_path', '!=', '');
                });

                if (Schema::hasColumn('users', 'enrollment_proof_path')) {
                    $docQuery->orWhere(function ($enrollmentQuery) {
                        $enrollmentQuery->whereNotNull('enrollment_proof_path')
                            ->where('enrollment_proof_path', '!=', '');
                    });
                }
            });
        };

        $students = User::query()
            ->where('role', 'student')
            ->where($studentsWithVerificationDocs)
            ->when($statusFilter !== 'all', function ($query) use ($statusFilter) {
                if ($statusFilter === 'pending') {
                    $query->where(function ($pendingQuery) {
                        $pendingQuery->where('school_id_verification_status', 'pending')
                            ->orWhereNull('school_id_verification_status')
                            ->orWhere('school_id_verification_status', '');
                    });

                    return;
                }

                $query->where('school_id_verification_status', $statusFilter);
            })
            ->orderByDesc('updated_at')
            ->paginate(15)
            ->withQueryString();

        $counts = [
            'pending' => User::query()
                ->where('role', 'student')
                ->where($studentsWithVerificationDocs)
                ->where(function ($pendingQuery) {
                    $pendingQuery->where('school_id_verification_status', 'pending')
                        ->orWhereNull('school_id_verification_status')
                        ->orWhere('school_id_verification_status', '');
                })
                ->count(),
            'approved' => User::query()
                ->where('role', 'student')
                ->where($studentsWithVerificationDocs)
                ->where('school_id_verification_status', 'approved')
                ->count(),
            'rejected' => User::query()
                ->where('role', 'student')
                ->where($studentsWithVerificationDocs)
                ->where('school_id_verification_status', 'rejected')
                ->count(),
        ];

        return view('admin.student_verifications.index', compact('students', 'statusFilter', 'counts'));
    }

    private function normalizeApprovalStatusFilter(string $statusFilter): string
    {
        $statusFilter = strtolower($statusFilter);
        $allowedStatuses = ['pending', 'approved', 'rejected', 'all'];

        return in_array($statusFilter, $allowedStatuses, true) ? $statusFilter : 'pending';
    }

    private function normalizeLandlordPermitFilter(string $statusFilter): string
    {
        $statusFilter = strtolower($statusFilter);
        $allowedStatuses = ['all', 'missing', 'pending', 'approved', 'rejected'];

        return in_array($statusFilter, $allowedStatuses, true) ? $statusFilter : 'all';
    }

    private function buildPermitApprovalPayload(string $statusFilter): array
    {
        $landlords = User::query()
            ->where('role', 'landlord')
            ->with([
                'landlordProfile.businessPermitReviewer:id,full_name',
                'landlordProfile.safetyCertificateReviewer:id,full_name',
            ])
            ->when($statusFilter !== 'all', function ($query) use ($statusFilter) {
                $query->where(function ($statusQuery) use ($statusFilter) {
                    $statusQuery->whereHas('landlordProfile', function ($profileQuery) use ($statusFilter) {
                        if ($statusFilter === 'missing') {
                            $profileQuery->where(function ($missingQuery) {
                                $missingQuery->whereNull('business_permit_path')
                                    ->orWhere('business_permit_path', '')
                                    ->orWhereNull('safety_certificate_path')
                                    ->orWhere('safety_certificate_path', '');
                            });

                            return;
                        }

                        $profileQuery->where(function ($documentQuery) use ($statusFilter) {
                            $documentQuery->where('business_permit_status', $statusFilter);

                            if ($this->supportsSafetyCertificateApproval()) {
                                $documentQuery->orWhere('safety_certificate_status', $statusFilter);
                            }
                        });
                    });

                    if ($statusFilter === 'missing') {
                        $statusQuery->orDoesntHave('landlordProfile');
                    }
                });
            })
            ->orderBy('full_name')
            ->paginate(15)
            ->withQueryString();

        $allLandlords = User::query()
            ->where('role', 'landlord')
            ->with('landlordProfile')
            ->get();

        $statusService = app(\App\Services\LandlordDocumentStatusService::class);
        $stats = $statusService->getAggregateStatistics();

        $counts = [
            'all' => $stats['total_landlords'],
            'missing' => $stats['landlords_with_missing_documents'],
            'pending' => $stats['landlords_with_pending_documents'],
            'approved' => $allLandlords->filter(fn ($landlord) => $statusService->resolveDocumentStatus($landlord?->landlordProfile, 'business') === 'approved'
                || $statusService->resolveDocumentStatus($landlord?->landlordProfile, 'safety') === 'approved')->count(),
            'rejected' => $stats['landlords_with_rejected_documents'],
        ];

        return [$landlords, $counts];
    }

    private function buildStudentApprovalPayload(string $statusFilter): array
    {
        $studentsWithVerificationDocs = function ($query) {
            $query->where(function ($docQuery) {
                $docQuery->where(function ($schoolIdQuery) {
                    $schoolIdQuery->whereNotNull('school_id_path')
                        ->where('school_id_path', '!=', '');
                });

                if (Schema::hasColumn('users', 'enrollment_proof_path')) {
                    $docQuery->orWhere(function ($enrollmentQuery) {
                        $enrollmentQuery->whereNotNull('enrollment_proof_path')
                            ->where('enrollment_proof_path', '!=', '');
                    });
                }
            });
        };

        $students = User::query()
            ->where('role', 'student')
            ->where($studentsWithVerificationDocs)
            ->when($statusFilter !== 'all', function ($query) use ($statusFilter) {
                if ($statusFilter === 'pending') {
                    $query->where(function ($pendingQuery) {
                        $pendingQuery->where('school_id_verification_status', 'pending')
                            ->orWhereNull('school_id_verification_status')
                            ->orWhere('school_id_verification_status', '');
                    });

                    return;
                }

                $query->where('school_id_verification_status', $statusFilter);
            })
            ->orderByDesc('updated_at')
            ->paginate(15)
            ->withQueryString();

        $counts = [
            'pending' => User::query()
                ->where('role', 'student')
                ->where($studentsWithVerificationDocs)
                ->where(function ($pendingQuery) {
                    $pendingQuery->where('school_id_verification_status', 'pending')
                        ->orWhereNull('school_id_verification_status')
                        ->orWhere('school_id_verification_status', '');
                })
                ->count(),
            'approved' => User::query()
                ->where('role', 'student')
                ->where($studentsWithVerificationDocs)
                ->where('school_id_verification_status', 'approved')
                ->count(),
            'rejected' => User::query()
                ->where('role', 'student')
                ->where($studentsWithVerificationDocs)
                ->where('school_id_verification_status', 'rejected')
                ->count(),
        ];

        return [$students, $counts];
    }

    private function buildPropertyApprovalPayload(string $statusFilter): array
    {
        $landlords = User::query()
            ->where('role', 'landlord')
            ->with([
                'landlordProfile',
                'properties' => function ($query) use ($statusFilter) {
                    if ($statusFilter !== 'all') {
                        $query->where('approval_status', $statusFilter);
                    }

                    $query->orderByDesc('created_at');
                },
            ])
            ->withCount('properties')
            ->withCount(['properties as pending_properties_count' => function ($query) {
                $query->where('approval_status', 'pending');
            }])
            ->withCount(['properties as approved_properties_count' => function ($query) {
                $query->where('approval_status', 'approved');
            }])
            ->withCount(['properties as rejected_properties_count' => function ($query) {
                $query->where('approval_status', 'rejected');
            }])
            ->orderBy('full_name')
            ->paginate(10)
            ->withQueryString();

        $counts = [
            'pending' => Property::where('approval_status', 'pending')->count(),
            'approved' => Property::where('approval_status', 'approved')->count(),
            'rejected' => Property::where('approval_status', 'rejected')->count(),
            'all' => Property::count(),
        ];

        return [$landlords, $counts];
    }

    private function supportsSafetyCertificateApproval(): bool
    {
        return app(\App\Services\LandlordDocumentStatusService::class)->supportsSafetyCertificateApproval();
    }

    private function resolveLandlordDocumentStatus(?LandlordProfile $profile, string $document): string
    {
        return app(\App\Services\LandlordDocumentStatusService::class)->resolveDocumentStatus($profile, $document);
    }

    public function adminApproveStudentVerification(User $user)
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            abort(403);
        }

        if (!Schema::hasColumn('users', 'school_id_verification_status')) {
            return back()->with('error', 'Student verification columns are not available yet. Run migrations first.');
        }

        if ($user->role !== 'student') {
            abort(404);
        }

        $hasVerificationDocument = filled($user->school_id_path)
            || (Schema::hasColumn('users', 'enrollment_proof_path') && filled($user->enrollment_proof_path));

        if (!$hasVerificationDocument) {
            return back()->with('error', 'No verification document was uploaded for this student yet.');
        }

        $user->update([
            'school_id_verification_status' => 'approved',
            'school_id_verified_at' => now(),
            'school_id_verified_by' => Auth::id(),
            'school_id_rejection_reason' => null,
        ]);

        $user->notify(new SystemNotification(
            'Academic verification approved',
            'Your School ID/COR/COE verification has been approved. Booking is now enabled for your account.',
            route('student.dashboard'),
            ['type' => 'student_verification_approved']
        ));

        return back()->with('success', 'Student academic verification approved successfully.');
    }

    public function adminRejectStudentVerification(Request $request, User $user)
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            abort(403);
        }

        if (!Schema::hasColumn('users', 'school_id_verification_status')) {
            return back()->with('error', 'Student verification columns are not available yet. Run migrations first.');
        }

        if ($user->role !== 'student') {
            abort(404);
        }

        $hasVerificationDocument = filled($user->school_id_path)
            || (Schema::hasColumn('users', 'enrollment_proof_path') && filled($user->enrollment_proof_path));

        if (!$hasVerificationDocument) {
            return back()->with('error', 'No verification document was uploaded for this student yet.');
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $user->update([
            'school_id_verification_status' => 'rejected',
            'school_id_verified_at' => now(),
            'school_id_verified_by' => Auth::id(),
            'school_id_rejection_reason' => $validated['rejection_reason'],
        ]);

        $user->notify(new SystemNotification(
            'Academic verification rejected',
            'Your School ID/COR/COE verification was rejected. Please check the reason and upload a corrected document in Student Setup.',
            route('student.setup.show'),
            [
                'type' => 'student_verification_rejected',
                'reason' => $validated['rejection_reason'],
            ]
        ));

        return back()->with('success', 'Student academic verification rejected and student notified.');
    }

    public function adminStudentDetails(User $user)
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            abort(403);
        }

        if ($user->role !== 'student') {
            abort(404);
        }

        // Get student's current booking if any
        $currentBooking = \App\Models\Booking::where('student_id', $user->id)
            ->where('status', 'approved')
            ->where('check_in', '<=', now()->toDateString())
            ->where('check_out', '>', now()->toDateString())
            ->with(['room.property'])
            ->first();

        // Get booking history
        $bookingHistory = \App\Models\Booking::where('student_id', $user->id)
            ->with(['room.property'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Get messages sent by student
        $messagesSent = \App\Models\Message::where('sender_id', $user->id)
            ->count();

        // Get messages received by student
        $messagesReceived = \App\Models\Message::where('receiver_id', $user->id)
            ->count();

        return view('admin.users.student_details', compact(
            'user', 'currentBooking', 'bookingHistory', 'messagesSent', 'messagesReceived'
        ));
    }

    public function adminEditStudent(User $user)
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            abort(403);
        }

        if ($user->role !== 'student') {
            abort(404);
        }

        return view('admin.users.student_edit', compact('user'));
    }

    public function adminUpdateStudent(Request $request, User $user)
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            abort(403);
        }

        if ($user->role !== 'student') {
            abort(404);
        }

        if (!$request->filled('program') && $request->filled('course')) {
            $request->merge(['program' => $request->input('course')]);
        }

        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'gender' => 'nullable|in:Male,Female,Other',
            'gender_other' => 'nullable|string|max:100',
            'contact_number' => 'nullable|string|max:20',
            'student_id' => 'nullable|string|max:50',
            'program' => 'nullable|string|max:255',
            'year_level' => 'nullable|in:1st Year,2nd Year,3rd Year,4th Year',
            'birth_date' => 'nullable|date',
            'address' => 'nullable|string|max:255',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_number' => 'nullable|string|max:20',
            'emergency_contact_relationship' => 'nullable|string|max:100',
            'parent_contact_name' => 'nullable|string|max:255',
            'guardian_name' => 'nullable|string|max:255',
            'guardian_contact' => 'nullable|string|max:20',
            'parent_contact_number' => 'nullable|string|max:20',
            'parent_contact_address' => 'nullable|string|max:500',
            'blood_type' => 'nullable|string|max:10',
            'allergies' => 'nullable|string|max:500',
            'medical_conditions' => 'nullable|string|max:500',
            'medications' => 'nullable|string|max:500',
        ]);

        // If gender is "Other", use the custom gender_other value
        if ($validated['gender'] === 'Other' && !empty($validated['gender_other'])) {
            $validated['gender'] = $validated['gender_other'];
        }
        
        // Remove gender_other from the update data
        unset($validated['gender_other']);

        $user->update($validated);

        return redirect()->route('admin.users.students.show', $user)
            ->with('success', 'Student profile updated successfully.');
    }

    public function adminProperties(Request $request)
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            abort(403);
        }

        $search = trim((string) $request->query('search', ''));
        $statusFilter = strtolower(trim((string) $request->query('status', 'all')));
        $availabilityFilter = strtolower(trim((string) $request->query('availability', 'all')));
        $locationFilter = trim((string) $request->query('location', 'all'));
        $activeView = strtolower(trim((string) $request->query('view', 'list')));
        if (!in_array($activeView, ['list', 'map'], true)) {
            $activeView = 'list';
        }

        $today = now()->toDateString();

        // Optimized query without eager loading full rooms collection
        $query = Property::query()
            ->with(['landlord:id,name,full_name,email,contact_number'])
            ->withCount([
                'rooms as total_rooms',
                'rooms as available_rooms' => function ($q) {
                    $q->where('status', 'available')->where('slots_available', '>', 0);
                },
                'rooms as occupied_rooms' => function ($q) use ($today) {
                    $q->whereHas('bookings', function ($bq) use ($today) {
                        $bq->where('status', 'approved')
                           ->whereDate('check_in', '<=', $today)
                           ->whereDate('check_out', '>', $today);
                    });
                },
            ]);

        // Search filter (property name, address, landlord name/email)
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%")
                  ->orWhereHas('landlord', function ($lq) use ($search) {
                      $lq->where('name', 'like', "%{$search}%")
                         ->orWhere('full_name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Approval status filter
        if ($statusFilter !== '' && $statusFilter !== 'all') {
            $query->where('approval_status', $statusFilter);
        }

        // Availability filter
        if ($availabilityFilter === 'available') {
            $query->whereHas('rooms', function ($rq) {
                $rq->where('status', 'available')->where('slots_available', '>', 0);
            });
        } elseif ($availabilityFilter === 'occupied') {
            $query->whereHas('rooms')->whereDoesntHave('rooms', function ($rq) {
                $rq->where('status', 'available')->where('slots_available', '>', 0);
            });
        } elseif ($availabilityFilter === 'no_rooms') {
            $query->whereDoesntHave('rooms');
        }

        // Location / Barangay filter
        if ($locationFilter !== '' && $locationFilter !== 'all') {
            $query->where('address', 'like', "%{$locationFilter}%");
        }

        // Paginated properties for table (persisting query string)
        $properties = (clone $query)
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        // Calculate occupancy rate for each paginated item
        $properties->getCollection()->transform(function ($property) {
            $property->occupancy_rate = $property->total_rooms > 0
                ? round(($property->occupied_rooms / $property->total_rooms) * 100, 1)
                : 0;
            return $property;
        });

        // Authoritative system-wide summary metrics
        $summary = [
            'total_properties' => Property::count(),
            'approved_properties' => Property::where('approval_status', 'approved')->count(),
            'pending_properties' => Property::where('approval_status', 'pending')->count(),
            'rejected_properties' => Property::where('approval_status', 'rejected')->count(),
            'available_rooms' => Room::where('status', 'available')->where('slots_available', '>', 0)->count(),
            'available_slots' => (int) Room::where('status', 'available')->sum('slots_available'),
        ];

        // Mapped properties for Map View (only needed columns to keep payload minimal)
        $mapProperties = (clone $query)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->select(['id', 'name', 'address', 'latitude', 'longitude', 'image_path', 'price_min', 'price_max', 'approval_status', 'landlord_id'])
            ->get()
            ->map(function ($p) {
                $totalRooms = (int) ($p->total_rooms ?? 0);
                $occupiedRooms = (int) ($p->occupied_rooms ?? 0);
                $availableRooms = (int) ($p->available_rooms ?? 0);
                $rate = $totalRooms > 0 ? round(($occupiedRooms / $totalRooms) * 100, 1) : 0;

                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'address' => $p->address,
                    'latitude' => (float) $p->latitude,
                    'longitude' => (float) $p->longitude,
                    'image_url' => $p->image_path ? file_url($p->image_path) : null,
                    'approval_status' => $p->approval_status ?? 'pending',
                    'landlord_name' => $p->landlord->full_name ?? $p->landlord->name ?? 'Landlord',
                    'price_min' => $p->price_min,
                    'price_max' => $p->price_max,
                    'total_rooms' => $totalRooms,
                    'available_rooms' => $availableRooms,
                    'occupied_rooms' => $occupiedRooms,
                    'occupancy_rate' => $rate,
                    'show_url' => route('admin.properties.show', $p->id),
                ];
            });

        // Distinct location values for filter dropdown
        $distinctLocations = Property::whereNotNull('address')
            ->where('address', '!=', '')
            ->distinct()
            ->pluck('address')
            ->map(function ($addr) {
                $parts = array_map('trim', explode(',', $addr));
                return $parts[0] ?? $addr;
            })
            ->filter()
            ->unique()
            ->sort()
            ->values();

        // Ordered list of all pending property IDs for inspector batch review navigation
        $pendingPropertyIds = Property::where('approval_status', 'pending')
            ->orderByDesc('id')
            ->pluck('id')
            ->values()
            ->all();

        // Selected deep-link property ID (e.g. from ?property=123)
        $selectedPropertyId = (int) $request->query('property', 0);

        return view('admin.properties.index', compact(
            'properties',
            'summary',
            'mapProperties',
            'distinctLocations',
            'search',
            'statusFilter',
            'availabilityFilter',
            'locationFilter',
            'activeView',
            'pendingPropertyIds',
            'selectedPropertyId'
        ));
    }

    public function landlordDashboard()
    {
        if (!Auth::check() || Auth::user()->role !== 'landlord') {
            abort(403);
        }
        $landlordId = Auth::id();
        $today = now()->toDateString();

        $properties = Property::where('landlord_id', $landlordId)
            ->withCount([
                'rooms as rooms_total_live',
                'rooms as rooms_vacant_live' => function ($q) {
                    $q->where('status', 'available')->where('slots_available', '>', 0);
                },
            ])
            ->with(['rooms' => function ($q) use ($today) {
                $q->with(['bookings' => function ($bookingQuery) use ($today) {
                    $bookingQuery->where('status', 'approved')
                        ->where('check_in', '<=', $today)
                        ->where('check_out', '>', $today)
                        ->with('student:id,full_name');
                }]);
            }])
            ->orderByDesc('created_at')
            ->get();

        $propertyIds = $properties->pluck('id');
        $propertiesCount = $properties->count();
        $totalRooms = Room::whereIn('property_id', $propertyIds)->count();
        $vacantRooms = Room::whereIn('property_id', $propertyIds)
            ->where('status', 'available')
            ->where('slots_available', '>', 0)
            ->count();
        $pendingRequests = Booking::where('status', 'pending')
            ->whereHas('room.property', function ($q) use ($landlordId) {
                $q->where('landlord_id', $landlordId);
            })
            ->count();

        $activeTenantBookings = Booking::with(['room.property', 'student'])
            ->where('status', 'approved')
            ->where('check_in', '<=', $today)
            ->where('check_out', '>', $today)
            ->whereHas('room.property', function ($q) use ($landlordId) {
                $q->where('landlord_id', $landlordId);
            })
            ->get();

        $activeTenantsCount = $activeTenantBookings->count();

        $recentCheckInsCount = Booking::where('status', 'approved')
            ->whereBetween('check_in', [now()->subDays(14)->toDateString(), $today])
            ->whereHas('room.property', function ($q) use ($landlordId) {
                $q->where('landlord_id', $landlordId);
            })
            ->count();

        $tenantTrend = collect(range(5, 0))->map(function (int $monthsAgo) use ($landlordId) {
            $monthDate = now()->subMonths($monthsAgo);
            $monthEnd = $monthDate->copy()->endOfMonth()->toDateString();

            $activeCount = Booking::where('status', 'approved')
                ->where('check_in', '<=', $monthEnd)
                ->where('check_out', '>', $monthEnd)
                ->whereHas('room.property', function ($q) use ($landlordId) {
                    $q->where('landlord_id', $landlordId);
                })
                ->count();

            return [
                'label' => $monthDate->format('M Y'),
                'count' => $activeCount,
            ];
        })->values();

        $propertyOccupancyBreakdown = $properties->map(function ($property) {
            $roomsBreakdown = $property->rooms->map(function ($room) {
                $activeRoomBookings = $room->bookings ?? collect();
                $tenantNames = $activeRoomBookings
                    ->pluck('student.full_name')
                    ->filter()
                    ->values();

                return [
                    'room_id' => $room->id,
                    'room_number' => $room->room_number,
                    'capacity' => max(1, (int) $room->capacity),
                    'occupied' => $tenantNames->count(),
                    'vacant' => max(0, max(1, (int) $room->capacity) - $tenantNames->count()),
                    'tenant_names' => $tenantNames,
                ];
            })->values();

            return [
                'property_id' => $property->id,
                'property_name' => $property->name,
                'active_tenants' => $roomsBreakdown->sum('occupied'),
                'rooms_total' => $roomsBreakdown->count(),
                'rooms' => $roomsBreakdown,
            ];
        })->values();

        $paymentWatchlist = Booking::with(['room.property', 'student'])
            ->where('status', 'approved')
            ->where('check_out', '>', $today)
            ->whereHas('room.property', function ($q) use ($landlordId) {
                $q->where('landlord_id', $landlordId);
            })
            ->get();

        $paymentWatchlist->each(function (Booking $booking) {
            $booking->effective_payment_status = $booking->derivedPaymentStatus();
            $booking->effective_due_date = $booking->resolvePaymentDueDate();
        });

        $overduePayments = $paymentWatchlist
            ->where('effective_payment_status', 'overdue')
            ->values();
        $overduePaymentsCount = $overduePayments->count();

        // Auto recommended rooms: top 6 cheapest available rooms across landlord properties
        $recommendedRooms = Room::with('property')
            ->whereIn('property_id', $propertyIds)
            ->where('status', 'available')
            ->where('slots_available', '>', 0)
            ->orderBy('price')
            ->limit(6)
            ->get();

        $unreadMessages = Message::where('receiver_id', $landlordId)->whereNull('read_at')->count();
        $unreadMessagesList = Message::with(['sender'])
            ->where('receiver_id', $landlordId)
            ->whereNull('read_at')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();
        $recentReceivedMessages = Message::with(['sender'])
            ->where('receiver_id', $landlordId)
            ->whereNotNull('read_at')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $setupSnapshot = $this->getLandlordSetupSnapshot(Auth::user());
        $setupChecklist = $this->makeLandlordSetupChecklist($setupSnapshot, false);
        $setupTotalCount = count($setupChecklist);
        $setupCompletedCount = collect($setupChecklist)->where('completed', true)->count();
        $needsLandlordSetup = $setupCompletedCount < $setupTotalCount;
        $needsPaymentSetup = !$setupSnapshot['billing_methods_complete'];

        // Documents & requirements summary (centralized landlord_documents records).
        $documentsSummary = \App\Models\LandlordDocument::where('landlord_id', $landlordId)
            ->current()
            ->get()
            ->map(fn (\App\Models\LandlordDocument $document) => [
                'type' => $document->document_type,
                'label' => $document->typeLabel($document->document_type),
                'verification_status' => $document->verification_status,
                'expiration' => $document->expirationInfo(),
            ])
            ->keyBy('type');

        return view('landlord.dashboard', compact(
            'properties','propertiesCount','totalRooms','vacantRooms','pendingRequests','recommendedRooms','unreadMessages','unreadMessagesList','recentReceivedMessages',
            'needsPaymentSetup',
            'setupChecklist', 'setupCompletedCount', 'setupTotalCount', 'needsLandlordSetup', 'setupSnapshot',
            'activeTenantsCount', 'recentCheckInsCount', 'tenantTrend', 'propertyOccupancyBreakdown',
            'overduePayments', 'overduePaymentsCount',
            'documentsSummary'
        ));
    }

    public function landlordProfile()
    {
        if (!Auth::check() || Auth::user()->role !== 'landlord') {
            abort(403);
        }

        $user = Auth::user()->loadMissing('landlordProfile');
        $setupSnapshot = $this->getLandlordSetupSnapshot($user);
        $setupChecklist = $this->makeLandlordSetupChecklist($setupSnapshot, true);

        $setupTotalCount = count($setupChecklist);
        $setupCompletedCount = collect($setupChecklist)->where('completed', true)->count();

        return view('landlord.profile.edit', compact(
            'user',
            'setupChecklist',
            'setupCompletedCount',
            'setupTotalCount',
            'setupSnapshot'
        ));
    }

    public function updateLandlordProfile(Request $request)
    {
        if (!Auth::check() || Auth::user()->role !== 'landlord') {
            abort(403);
        }

        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'profile_image' => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:2048',
            'contact_number' => 'required|string|max:20',
            'boarding_house_name' => 'required|string|max:255',
            'about' => 'required|string|max:1000',
            'business_permit' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'payment_bank_name' => 'nullable|string|max:255',
            'payment_account_name' => 'nullable|string|max:255',
            'payment_account_number' => 'nullable|string|max:100',
            'payment_gcash_number' => 'nullable|string|max:20',
            'payment_gcash_name' => 'nullable|string|max:255',
            'payment_gcash_qr' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'contract_signature_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'contract_signature_data' => 'nullable|string',
            'payment_instructions' => 'nullable|string|max:1000',
            'preferred_payment_methods' => 'nullable|array',
            'preferred_payment_methods.*' => 'in:bank,gcash,cash',
            'current_password' => 'nullable|required_with:new_password',
            'new_password' => 'nullable|min:8|confirmed',
        ]);

        try {
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
        } catch (\Symfony\Component\Mime\Exception\LogicException $e) {
            return back()->withInput()->with('error', 'File upload validation failed. Please ensure the PHP "fileinfo" extension is enabled and try a smaller image if needed (PHP upload limit).');
        }

        $preferredPaymentMethods = collect($request->input('preferred_payment_methods', []))
            ->filter(fn ($method) => in_array($method, ['bank', 'gcash', 'cash'], true))
            ->values();

        $validationErrors = [];
        if ($preferredPaymentMethods->contains('bank')) {
            if (!filled($request->input('payment_bank_name'))) {
                $validationErrors['payment_bank_name'] = 'Bank name is required when Bank is selected as a payment method.';
            }
            if (!filled($request->input('payment_account_name'))) {
                $validationErrors['payment_account_name'] = 'Account name is required when Bank is selected as a payment method.';
            }
        }

        if ($preferredPaymentMethods->contains('gcash')) {
            if (!filled($request->input('payment_gcash_name'))) {
                $validationErrors['payment_gcash_name'] = 'GCash name is required when GCash is selected as a payment method.';
            }
            if (!filled($request->input('payment_gcash_number'))) {
                $validationErrors['payment_gcash_number'] = 'GCash number is required when GCash is selected as a payment method.';
            }
        }

        if (!empty($validationErrors)) {
            return back()->withErrors($validationErrors)->withInput();
        }

        // Update basic info
        $user->update([
            'full_name' => $request->full_name,
            'email' => $request->email,
            'contact_number' => $request->contact_number,
            'boarding_house_name' => $request->boarding_house_name,
        ]);

        $landlordProfile = $user->landlordProfile()->firstOrCreate(
            ['user_id' => $user->id],
            [
                'contact_number' => $request->contact_number,
                'boarding_house_name' => $request->boarding_house_name,
            ]
        );

        $profileData = [
            'contact_number' => $request->contact_number,
            'boarding_house_name' => $request->boarding_house_name,
            'about' => $request->about,
            'payment_bank_name' => $request->payment_bank_name,
            'payment_account_name' => $request->payment_account_name,
            'payment_account_number' => $request->payment_account_number,
            'payment_gcash_number' => $request->payment_gcash_number,
            'payment_gcash_name' => $request->payment_gcash_name,
            'payment_instructions' => $request->payment_instructions,
            'preferred_payment_methods' => $preferredPaymentMethods->all(),
        ];

        if ($request->hasFile('business_permit')) {
            $profileData['business_permit_path'] = app(\App\Services\FileStorageService::class)->replace(
                $landlordProfile->business_permit_path,
                $request->file('business_permit'),
                'landlords/' . $user->id . '/business-permits'
            );
            $profileData['business_permit_status'] = 'pending';
            $profileData['business_permit_reviewed_at'] = null;
            $profileData['business_permit_reviewed_by'] = null;
            $profileData['business_permit_rejection_reason'] = null;
        }

        if ($request->hasFile('payment_gcash_qr')) {
            $profileData['payment_gcash_qr_path'] = app(\App\Services\FileStorageService::class)->replace(
                $landlordProfile->payment_gcash_qr_path,
                $request->file('payment_gcash_qr'),
                'landlords/' . $user->id . '/payment-qr'
            );
        }

        $signatureData = trim((string) $request->input('contract_signature_data', ''));
        if ($signatureData !== '') {
            $storedSignaturePath = $this->storeLandlordProfileSignatureDataUrl(
                $signatureData,
                'landlords/' . $user->id . '/signatures',
                $landlordProfile->contract_signature_path
            );

            if (!$storedSignaturePath) {
                return back()->withErrors([
                    'contract_signature_data' => 'Invalid signature drawing. Please draw again or upload an image file.',
                ])->withInput();
            }

            $profileData['contract_signature_path'] = $storedSignaturePath;
        } elseif ($request->hasFile('contract_signature_image')) {
            $profileData['contract_signature_path'] = app(\App\Services\FileStorageService::class)->replace(
                $landlordProfile->contract_signature_path,
                $request->file('contract_signature_image'),
                'landlords/' . $user->id . '/signatures'
            );
        }

        $profileCompleted = filled($request->contact_number)
            && filled($request->boarding_house_name)
            && filled($request->about);
        $bankRequired = $preferredPaymentMethods->contains('bank');
        $gcashRequired = $preferredPaymentMethods->contains('gcash');
        $cashRequired = $preferredPaymentMethods->contains('cash');

        $bankReady = !$bankRequired || (filled($request->payment_bank_name) && filled($request->payment_account_name));
        $gcashReady = !$gcashRequired || (
            filled($request->payment_gcash_name)
            && filled($request->payment_gcash_number)
        );
        $cashReady = !$cashRequired || true;

        $billingCompleted = $preferredPaymentMethods->isNotEmpty() && $bankReady && $gcashReady && $cashReady;

        $profileData['profile_completed'] = $profileCompleted;
        $profileData['billing_completed'] = $billingCompleted;

        if (empty($profileData['business_permit_status']) && empty($landlordProfile->business_permit_status)) {
            $profileData['business_permit_status'] = filled($profileData['business_permit_path'] ?? $landlordProfile->business_permit_path)
                ? 'pending'
                : 'not_submitted';
        }

        $landlordProfile->update($profileData);

        if ($request->hasFile('profile_image')) {
            try {
                $user->profile_image_path = app(\App\Services\FileStorageService::class)->replace(
                    $user->profile_image_path,
                    $request->file('profile_image'),
                    'landlords/' . $user->id . '/profile'
                );
                $user->save();
            } catch (\RuntimeException $e) {
                return back()->withInput()->with('error', 'File upload failed. Please try again.');
            } catch (\Symfony\Component\Mime\Exception\LogicException $e) {
                return back()->withInput()->with('error', 'Unable to process the uploaded image. Please ensure the PHP "fileinfo" extension is enabled and try a smaller image if needed (PHP upload limit).');
            }
        }

        // Update password if provided
        if ($request->filled('new_password')) {
            if (!Hash::check((string) $request->input('current_password'), (string) $user->password)) {
                return back()->withErrors(['current_password' => 'Current password is incorrect.'])->withInput();
            }
            $user->update(['password' => Hash::make($request->new_password)]);
        }

        return back()->with('success', 'Profile updated successfully.');
    }

    private function storeLandlordProfileSignatureDataUrl(string $signatureData, string $directory, ?string $oldPath = null): ?string
    {
        $signatureData = trim($signatureData);
        if ($signatureData === '') {
            return null;
        }

        if (!preg_match('/^data:image\/(png|jpeg|jpg|webp);base64,(.+)$/i', $signatureData, $matches)) {
            return null;
        }

        $mime = strtolower((string) $matches[1]);
        $encoded = (string) $matches[2];
        $binary = base64_decode($encoded, true);

        if ($binary === false || $binary === '') {
            return null;
        }

        if (strlen($binary) > (5 * 1024 * 1024)) {
            return null;
        }

        $extension = $mime === 'jpg' ? 'jpeg' : $mime;

        $path = app(\App\Services\FileStorageService::class)->put(
            $directory,
            $binary,
            $extension
        );

        if (!empty($oldPath)) {
            app(\App\Services\FileStorageService::class)->delete($oldPath);
        }

        return $path;
    }

    public function adminSettings()
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            abort(403);
        }

        $user = Auth::user();
        return view('admin.settings.edit', compact('user'));
    }

    public function updateAdminSettings(Request $request)
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            abort(403);
        }

        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'profile_image' => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:2048',
            'contact_number' => 'nullable|string|max:20',
            'current_password' => 'nullable|required_with:new_password',
            'new_password' => 'nullable|min:8|confirmed',
        ]);

        try {
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
        } catch (\Symfony\Component\Mime\Exception\LogicException $e) {
            return back()->withInput()->with('error', 'File upload validation failed. Please ensure the PHP "fileinfo" extension is enabled and try a smaller image if needed (PHP upload limit).');
        }

        $user->update([
            'full_name' => $request->full_name,
            'email' => $request->email,
            'contact_number' => $request->contact_number,
        ]);

        if ($request->hasFile('profile_image')) {
            try {
                $user->profile_image_path = app(\App\Services\FileStorageService::class)->replace(
                    $user->profile_image_path,
                    $request->file('profile_image'),
                    'users/' . $user->id . '/profile'
                );
                $user->save();
            } catch (\RuntimeException $e) {
                return back()->withInput()->with('error', 'File upload failed. Please try again.');
            } catch (\Symfony\Component\Mime\Exception\LogicException $e) {
                return back()->withInput()->with('error', 'Unable to process the uploaded image. Please ensure the PHP "fileinfo" extension is enabled and try a smaller image if needed (PHP upload limit).');
            }
        }

        if ($request->filled('new_password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'Current password is incorrect.']);
            }
            $user->update(['password' => Hash::make($request->new_password)]);
        }

        return back()->with('success', 'Admin settings updated successfully.');
    }

    public function studentDashboard(Request $request)
    {
        if (!Auth::check() || Auth::user()->role !== 'student') {
            abort(403);
        }
        $student = Auth::user();
        $today = now()->toDateString();

        $activeCompletedOnboarding = TenantOnboarding::query()
            ->where('status', 'completed')
            ->whereHas('booking', function ($q) use ($student, $today) {
                $q->where('student_id', $student->id)
                    ->where('status', 'approved')
                    ->where('check_out', '>', $today);
            })
            ->exists();

        if ($activeCompletedOnboarding) {
            return redirect()->route('student.tenant.dashboard');
        }

        // Treat any non-ended approved booking as "current" (even if check-in is in the future)
        $currentApprovedBooking = $student->bookings()
            ->where('status', 'approved')
            ->where('check_out', '>', $today)
            ->with(['room.property.landlord', 'tenantOnboarding'])
            ->orderByDesc('check_in')
            ->first();
        $hasCurrentApprovedBooking = !empty($currentApprovedBooking);
        $currentApprovedOnboardingStatus = strtolower((string) ($currentApprovedBooking?->tenantOnboarding?->status ?? ''));
        $showApprovedBookingModal = $hasCurrentApprovedBooking
            && $currentApprovedOnboardingStatus !== 'completed';

        $activeBookingsCount = $student->bookings()
            ->where('status', 'approved')
            ->where('check_out', '>', $today)
            ->count();
        $pendingBookingsCount = $student->bookings()->where('status','pending')->count();
        $messagesCount = $student->messagesReceived()->count() + $student->messagesSent()->count();
        $unreadMessagesCount = \App\Models\Message::where('receiver_id', $student->id)->whereNull('read_at')->count();
        $notificationsCount = Schema::hasTable('notifications') ? $student->unreadNotifications()->count() : 0;

        // Reports data
        $totalReports = $student->reports()->count();
        $reportsWithResponses = $student->reports()->whereNotNull('admin_response')->count();
        $unreadResponsesCount = $student->reports()->whereNotNull('admin_response')->where('response_read', false)->count();
        $canSubmitReport = $student->bookings()
            ->where('status', 'approved')
            ->whereDate('check_in', '<=', $today)
            ->exists();

        $ratingReminders = Booking::query()
            ->with(['room.property'])
            ->where('student_id', $student->id)
            ->where('status', 'approved')
            ->whereDate('check_out', '<=', $today)
            ->whereHas('room', function ($query) use ($student) {
                $query->whereDoesntHave('feedbacks', function ($feedbackQuery) use ($student) {
                    $feedbackQuery->where('user_id', $student->id);
                });
            })
            ->orderByDesc('check_out')
            ->limit(5)
            ->get();

        // Recent activity panels
        $recentBookings = $student->bookings()
            ->with(['room.property.landlord'])
            ->latest()
            ->limit(10)
            ->get();

        $recentMessages = $student->messagesReceived()
            ->with(['sender'])
            ->latest()
            ->limit(10)
            ->get();

        // Build conversation threads: group all messages (sent + received) by (other_person, property)
        $allMessages = \App\Models\Message::with(['sender:id,full_name', 'receiver:id,full_name', 'property:id,name'])
            ->where(function ($q) use ($student) {
                $q->where('sender_id', $student->id)
                  ->orWhere('receiver_id', $student->id);
            })
            ->latest()
            ->limit(200)
            ->get();

        // Group into threads: key = "otherId_propertyId"
        $threads = [];
        foreach ($allMessages as $msg) {
            $otherId = (int)$msg->sender_id === $student->id ? $msg->receiver_id : $msg->sender_id;
            $propId  = $msg->property_id ?? 0;
            $key     = $otherId . '_' . $propId;
            if (!isset($threads[$key])) {
                $threads[$key] = [
                    'other'         => (int)$msg->sender_id === $student->id ? $msg->receiver : $msg->sender,
                    'property'      => $msg->property,
                    'property_id'   => $msg->property_id,
                    'latest'        => $msg,
                    'unread'        => 0,
                    'messages'      => [],
                ];
            }
            if (empty($msg->read_at) && (int)$msg->receiver_id === $student->id) {
                $threads[$key]['unread']++;
            }
            $threads[$key]['messages'][] = $msg;
        }
        $messageThreads = collect(array_values($threads));

        $latestOnboarding = \App\Models\TenantOnboarding::query()
            ->whereHas('booking', function ($q) use ($student) {
                $q->where('student_id', $student->id);
            })
            ->with(['booking.room.property.landlord'])
            ->latest()
            ->first();

        $allOnboardings = \App\Models\TenantOnboarding::query()
            ->whereHas('booking', function ($q) use ($student) {
                $q->where('student_id', $student->id);
            })
            ->with(['booking.room.property.landlord'])
            ->orderByDesc('created_at')
            ->get();

        $leaveRequests = collect();
        if (Schema::hasTable('leave_requests')) {
            $leaveRequests = LeaveRequest::query()
                ->where('student_id', $student->id)
                ->with(['booking.room.property.landlord'])
                ->orderByDesc('created_at')
                ->get();
        }

        $currentBookingLeaveRequests = collect();
        if (!empty($currentApprovedBooking)) {
            $currentBookingLeaveRequests = $leaveRequests->where('booking_id', $currentApprovedBooking->id)->values();
        }

        // Roommates panel: onboarded students in the same room
        $roommates = collect();
        $roommatesCount = 0;
        $roomCapacity = null;
        if (!empty($currentApprovedBooking) && !empty($currentApprovedBooking->room_id)) {
            $roomCapacity = $currentApprovedBooking->room?->capacity;

            $roommates = Booking::query()
                ->where('room_id', $currentApprovedBooking->room_id)
                ->where('status', 'approved')
                ->where(function ($q) use ($today) {
                    $q->whereNull('check_out')
                        ->orWhereDate('check_out', '>=', $today);
                })
                ->whereHas('tenantOnboarding')
                ->with(['student'])
                ->orderByDesc('check_in')
                ->get();

            $roommatesCount = (int) $roommates->count();
        }

        $recentReports = $student->reports()
            ->latest()
            ->limit(10)
            ->get();

        // Properties the student has booked (used to restrict messaging to the property owner)
        $messageProperties = \App\Models\Property::query()
            ->join('rooms', 'rooms.property_id', '=', 'properties.id')
            ->join('bookings', 'bookings.room_id', '=', 'rooms.id')
            ->where('bookings.student_id', $student->id)
            ->whereIn('bookings.status', ['pending', 'approved'])
            ->select('properties.id', 'properties.name', 'properties.landlord_id')
            ->distinct()
            ->orderBy('properties.name')
            ->get();

        // Personalize: infer preferred capacity from latest approved booking
        $latestApproved = $student->bookings()
            ->where('status','approved')
            ->latest()
            ->with('room')
            ->first();
        $preferredCapacity = $latestApproved && $latestApproved->room ? max(1, (int)$latestApproved->room->capacity) : 1;

        // Filters from query string
        $minPrice = $request->query('min_price');
        $maxPrice = $request->query('max_price');
        $minCapacity = $request->query('capacity');
        $search = $request->query('q');

        // Recommended rooms (rooms with available slots; apply user filters if provided)
        // Don't filter by status='available' - instead check actual occupancy
        $recommendedRooms = Room::with('property.landlord')
            ->where('status', '!=', 'maintenance') // Exclude maintenance rooms
            ->whereHas('property', function ($q) {
                $q->visibleToAudience();
            })
            ->when($minCapacity !== null && $minCapacity !== '', function ($q) use ($minCapacity) {
                $q->where('capacity', '>=', (int) $minCapacity);
            })
            ->when($minPrice !== null && $minPrice !== '', function ($q) use ($minPrice) {
                $q->where('price', '>=', (float) $minPrice);
            })
            ->when($maxPrice !== null && $maxPrice !== '', function ($q) use ($maxPrice) {
                $q->where('price', '<=', (float) $maxPrice);
            })
            ->when($search && $search !== '', function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('room_number', 'like', "%$search%")
                       ->orWhereHas('property', fn($pq) => $pq->where('name', 'like', "%$search%")->orWhere('address', 'like', "%$search%"));
                });
            })
            ->orderBy('price')
            ->get()
            ->filter(fn($room) => $room->hasAvailableSlots()) // Filter by actual occupancy
            ->values()
            ->take(6);

        // All rooms with occupancy info (exclude maintenance)
        $allRooms = Room::with('property.landlord')
            ->where('status', '!=', 'maintenance') // Exclude maintenance rooms
            ->whereHas('property', function ($q) {
                $q->visibleToAudience();
            })
            ->when($minCapacity !== null && $minCapacity !== '', function ($q) use ($minCapacity) {
                $q->where('capacity', '>=', (int) $minCapacity);
            })
            ->when($minPrice !== null && $minPrice !== '', function ($q) use ($minPrice) {
                $q->where('price', '>=', (float) $minPrice);
            })
            ->when($maxPrice !== null && $maxPrice !== '', function ($q) use ($maxPrice) {
                $q->where('price', '<=', (float) $maxPrice);
            })
            ->when($search && $search !== '', function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('room_number', 'like', "%$search%")
                       ->orWhereHas('property', fn($pq) => $pq->where('name', 'like', "%$search%")->orWhere('address', 'like', "%$search%"));
                });
            })
            ->orderBy('property_id')
            ->orderBy('room_number')
            ->get();

        $newThreshold = now()->subDays(3);

        // All properties with live counts for student reference (boarding houses directory)
        $allProperties = Property::with(['landlord:id,full_name'])
            ->visibleToAudience()
            ->withCount([
                'rooms as rooms_total_live',
                'rooms as rooms_available_live' => function($q){
                    $q->where('status','available')->where('slots_available', '>', 0);
                },
            ])
            ->when($search && $search !== '', function($q) use ($search){
                $q->where(function($qq) use ($search){
                    $qq->where('name','like',"%$search%")
                       ->orWhere('address','like',"%$search%");
                });
            })
            ->orderBy('name')
            ->get();

        return view('student.dashboard', compact(
            'activeBookingsCount','pendingBookingsCount','messagesCount','unreadMessagesCount','notificationsCount',
            'recommendedRooms','allRooms','preferredCapacity','minPrice','maxPrice','minCapacity','newThreshold','allProperties',
            'totalReports','reportsWithResponses','unreadResponsesCount',
            'canSubmitReport','ratingReminders',
            'recentBookings','recentMessages','latestOnboarding','allOnboardings','recentReports',
            'messageProperties','messageThreads',
            'currentApprovedBooking','hasCurrentApprovedBooking',
            'showApprovedBookingModal',
            'leaveRequests','currentBookingLeaveRequests',
            'roommates','roommatesCount','roomCapacity'
        ));
    }

    public function studentTenantDashboard()
    {
        if (!Auth::check() || Auth::user()->role !== 'student') {
            abort(403);
        }

        $student = Auth::user();
        $today = now()->toDateString();

        $currentApprovedBooking = $student->bookings()
            ->where('status', 'approved')
            ->where('check_out', '>', $today)
            ->with(['room.property.landlord', 'tenantOnboarding'])
            ->orderByDesc('check_in')
            ->first();
        $hasCurrentApprovedBooking = !empty($currentApprovedBooking);

        $latestOnboarding = TenantOnboarding::query()
            ->whereHas('booking', function ($q) use ($student) {
                $q->where('student_id', $student->id);
            })
            ->with(['booking.room.property.landlord'])
            ->latest()
            ->first();

        $tenantOnboarding = TenantOnboarding::query()
            ->where('status', 'completed')
            ->whereHas('booking', function ($q) use ($student, $today) {
                $q->where('student_id', $student->id)
                    ->where('status', 'approved')
                    ->where('check_out', '>', $today);
            })
            ->with(['booking.room.property.landlord'])
            ->orderByDesc('updated_at')
            ->first();

        if (empty($tenantOnboarding) || empty($tenantOnboarding->booking?->room?->property)) {
            return redirect()->route('student.dashboard')
                ->with('info', 'Tenant dashboard is available once your onboarding is completed and your approved booking is still valid.');
        }

        $tenantBooking = $tenantOnboarding->booking;
        $tenantRoom = $tenantBooking->room;
        $tenantProperty = $tenantRoom->property;
        $landlord = $tenantProperty->landlord;

        $lat = $tenantProperty->latitude;
        $lng = $tenantProperty->longitude;
        $addressQuery = trim((string) ($tenantProperty->address ?? ''));

        if ($lat !== null && $lng !== null && $lat !== '' && $lng !== '') {
            $mapQuery = trim($lat . ',' . $lng);
        } else {
            $mapQuery = trim((string) ($tenantProperty->name ?? '') . ' ' . $addressQuery);
        }

        if ($mapQuery === '') {
            $mapQuery = 'Mindoro State University';
        }

        $encodedMapQuery = urlencode($mapQuery);
        $mapEmbedUrl = 'https://maps.google.com/maps?q=' . $encodedMapQuery . '&z=15&output=embed';
        $mapOpenUrl = 'https://www.google.com/maps/search/?api=1&query=' . $encodedMapQuery;

        $monthlyRent = (float) (($tenantBooking->monthly_rent_amount ?? 0) > 0
            ? $tenantBooking->monthly_rent_amount
            : ($tenantRoom->price ?? 0));
        $paymentDueDate = $tenantBooking->resolvePaymentDueDate();
        $paymentStatus = $tenantBooking->derivedPaymentStatus();

        $unreadMessagesCount = Message::query()
            ->where('receiver_id', $student->id)
            ->whereNull('read_at')
            ->count();
        $unreadResponsesCount = $student->reports()
            ->whereNotNull('admin_response')
            ->where('response_read', false)
            ->count();
        $pendingBookingsCount = $student->bookings()
            ->where('status', 'pending')
            ->count();

        $roommates = Booking::query()
            ->where('room_id', $tenantRoom->id)
            ->where('status', 'approved')
            ->whereDate('check_in', '<=', $today)
            ->whereDate('check_out', '>', $today)
            ->whereHas('tenantOnboarding', function ($query) {
                $query->where('status', 'completed');
            })
            ->with(['student'])
            ->orderBy('check_in')
            ->get();
        $roommatesCount = (int) $roommates->count();
        $roomCapacity = $tenantRoom->capacity;

        $tenantLeaveRequests = collect();
        if (Schema::hasTable('leave_requests')) {
            $tenantLeaveRequests = LeaveRequest::query()
                ->where('student_id', $student->id)
                ->where('booking_id', $tenantBooking->id)
                ->with(['booking.room.property.landlord'])
                ->orderByDesc('created_at')
                ->get();
        }

        $houseRules = collect($tenantProperty->house_rules ?? [])->filter()->values();
        $buildingInclusions = collect($tenantProperty->building_inclusions ?? [])->filter()->values();

        $feedbackBaseQuery = RoomFeedback::query()
            ->where('user_id', $student->id)
            ->where('room_id', $tenantRoom->id);

        $feedbackTotalCount = (clone $feedbackBaseQuery)->count();
        $feedbackAverageRating = (clone $feedbackBaseQuery)->avg('rating');
        $feedbackPositiveCount = (clone $feedbackBaseQuery)
            ->where('sentiment_label', 'positive')
            ->count();
        $recentFeedbackItems = (clone $feedbackBaseQuery)
            ->with(['room.property'])
            ->latest()
            ->limit(5)
            ->get();

        $reportBaseQuery = Report::query()->where('user_id', $student->id);
        $reportTotalCount = (clone $reportBaseQuery)->count();
        $reportPendingCount = (clone $reportBaseQuery)->where('status', 'pending')->count();
        $reportInProgressCount = (clone $reportBaseQuery)->where('status', 'in_progress')->count();
        $reportResolvedCount = (clone $reportBaseQuery)->where('status', 'resolved')->count();
        $reportUnreadResponseCount = (clone $reportBaseQuery)
            ->whereNotNull('admin_response')
            ->where('response_read', false)
            ->count();
        $recentUserReports = (clone $reportBaseQuery)
            ->latest()
            ->limit(6)
            ->get();

        return view('student.tenant-dashboard', compact(
            'tenantOnboarding', 'tenantBooking', 'tenantRoom', 'tenantProperty', 'landlord',
            'mapEmbedUrl', 'mapOpenUrl',
            'monthlyRent', 'paymentDueDate', 'paymentStatus',
            'unreadMessagesCount', 'unreadResponsesCount', 'pendingBookingsCount',
            'roommates', 'roommatesCount', 'roomCapacity',
            'tenantLeaveRequests', 'houseRules', 'buildingInclusions',
            'feedbackTotalCount', 'feedbackAverageRating', 'feedbackPositiveCount', 'recentFeedbackItems',
            'reportTotalCount', 'reportPendingCount', 'reportInProgressCount', 'reportResolvedCount',
            'reportUnreadResponseCount', 'recentUserReports',
            'latestOnboarding', 'currentApprovedBooking', 'hasCurrentApprovedBooking'
        ));
    }

    private function getLandlordSetupSnapshot(User $user): array
    {
        $user->loadMissing('landlordProfile');
        $landlordProfile = $user->landlordProfile;

        $propertyIds = Property::where('landlord_id', $user->id)->pluck('id');
        $propertiesCount = $propertyIds->count();
        $totalRooms = Room::whereIn('property_id', $propertyIds)->count();
        $roomsPricedCount = Room::whereIn('property_id', $propertyIds)
            ->where('price', '>', 0)
            ->count();

        $preferredPaymentMethods = collect(optional($landlordProfile)->preferred_payment_methods ?? [])
            ->filter(fn ($method) => in_array($method, ['bank', 'gcash', 'cash'], true))
            ->values();

        $bankSetupComplete = filled(optional($landlordProfile)->payment_bank_name)
            && filled(optional($landlordProfile)->payment_account_name);
        $gcashSetupComplete = filled(optional($landlordProfile)->payment_gcash_name)
            && filled(optional($landlordProfile)->payment_gcash_number);

        $requiresBank = $preferredPaymentMethods->contains('bank');
        $requiresGcash = $preferredPaymentMethods->contains('gcash');
        $requiresCash = $preferredPaymentMethods->contains('cash');
        $paymentMethodChosen = $preferredPaymentMethods->isNotEmpty();

        $bankRequirementMet = !$requiresBank || $bankSetupComplete;
        $gcashRequirementMet = !$requiresGcash || $gcashSetupComplete;
        $cashRequirementMet = !$requiresCash || true;
        $billingMethodsComplete = $paymentMethodChosen && $bankRequirementMet && $gcashRequirementMet && $cashRequirementMet;

        $profileComplete = filled($user->contact_number)
            && filled($user->boarding_house_name)
            && filled(optional($landlordProfile)->about);

        $permitUploaded = filled(optional($landlordProfile)->business_permit_path);
        $permitStatus = (string) (optional($landlordProfile)->business_permit_status ?: ($permitUploaded ? 'pending' : 'not_submitted'));
        if (!$permitUploaded) {
            $permitStatus = 'not_submitted';
        }
        $permitApproved = $permitUploaded && $permitStatus === 'approved';

        $hasPropertyLocation = $propertiesCount > 0;
        $hasRoomAvailability = $totalRooms > 0;
        $pricingBasisComplete = $totalRooms > 0 && $roomsPricedCount === $totalRooms;

        return [
            'profile_complete' => $profileComplete,
            'permit_uploaded' => $permitUploaded,
            'permit_approved' => $permitApproved,
            'permit_status' => $permitStatus,
            'permit_rejection_reason' => (string) (optional($landlordProfile)->business_permit_rejection_reason ?? ''),
            'has_property_location' => $hasPropertyLocation,
            'has_room_availability' => $hasRoomAvailability,
            'pricing_basis_complete' => $pricingBasisComplete,
            'billing_methods_complete' => $billingMethodsComplete,
            'setup_submitted' => $profileComplete && $billingMethodsComplete,
            'billing_complete' => $billingMethodsComplete,
        ];
    }

    private function makeLandlordSetupChecklist(array $setupSnapshot, bool $forProfile = false): array
    {
        $profileUrl = $forProfile
            ? '#personal-info'
            : route('landlord.setup.show', ['step' => 'profile']);
        $verificationUrl = $forProfile
            ? '#business-verification'
            : route('landlord.setup.show', ['step' => 'permit']);
        $paymentUrl = $forProfile
            ? '#payment-details'
            : route('landlord.setup.show', ['step' => 'billing']);

        return [
            [
                'title' => 'Complete landlord profile',
                'description' => 'Add contact number, boarding house name, and short description.',
                'completed' => $setupSnapshot['profile_complete'],
                'action_label' => 'Open Personal Info',
                'action_url' => $profileUrl,
            ],
            [
                'title' => 'Upload business permit (optional)',
                'description' => 'Optional. Upload a business permit if available for admin review.',
                'completed' => true,
                'action_label' => 'Open Permit Step',
                'action_url' => $verificationUrl,
            ],
            [
                'title' => 'Finalize billing setup',
                'description' => 'Set your preferred payment methods and account details.',
                'completed' => $setupSnapshot['billing_methods_complete'],
                'action_label' => 'Open Billing Step',
                'action_url' => $paymentUrl,
            ],
            [
                'title' => 'Add property location',
                'description' => 'Create at least one property with address and map coordinates.',
                'completed' => $setupSnapshot['has_property_location'],
                'action_label' => 'Add Property',
                'action_url' => route('landlord.properties.create'),
            ],
            [
                'title' => 'Set room availability',
                'description' => 'Add room capacity and status to make listings bookable.',
                'completed' => $setupSnapshot['has_room_availability'],
                'action_label' => 'Manage Rooms',
                'action_url' => route('landlord.rooms.index'),
            ],
        ];
    }
}
