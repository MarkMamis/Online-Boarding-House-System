<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Property;
use App\Models\Room;
use App\Models\Booking;
use App\Models\Message;
use App\Models\LeaveRequest;
use Illuminate\Support\Facades\Schema;
use App\Models\LandlordProfile;
use App\Models\TenantOnboarding;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

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

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();

            if (Schema::hasColumn('users', 'is_active') && !$user->is_active) {
                Auth::logout();
                return back()->withErrors(['email' => 'Your account is deactivated. Please contact the administrator.'])->withInput();
            }

            if (
                $user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail
                && $user->role !== 'admin'
                && !$user->hasVerifiedEmail()
            ) {
                return redirect()->route('verification.notice');
            }

            switch ($user->role) {
                case 'admin':
                    return redirect('/admin/dashboard');
                case 'landlord':
                    return redirect('/landlord/dashboard');
                case 'student':
                    return redirect('/student/dashboard');
                default:
                    return redirect('/student/dashboard');
            }
        }

        return back()->withErrors(['email' => 'Invalid credentials'])->withInput();
    }

    public function showRegisterForm()
    {
        return view('auth.register');
    }

    public function showRegisterStudentForm()
    {
        return view('auth.register_student');
    }

    public function showRegisterLandlordForm()
    {
        return view('auth.register_landlord');
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
            'contact_number' => 'required|string|max:20',
            'course' => 'required_if:role,student|string|max:255',
            'year_level' => 'required_if:role,student|in:1st Year,2nd Year,3rd Year,4th Year',
            'gender' => 'required_if:role,student|in:Male,Female,Other,Rather not say',
            'gender_custom' => 'nullable|string|max:100',
            'boarding_house_name' => 'required_if:role,landlord|string|max:255',
            // Role is now hidden in the form
            'role' => 'required|in:landlord,student',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Enforce role safety: ignore any attempt to submit 'admin'
        $role = in_array($request->input('role'), ['landlord','student'], true)
            ? $request->input('role')
            : 'student';

        // Handle gender field - if "Other" is selected, use the custom value
        $gender = null;
        if ($role === 'student') {
            if ($request->gender === 'Other' && !empty($request->gender_custom)) {
                $gender = $request->gender_custom;
            } elseif ($request->gender === 'Rather not say') {
                $gender = null;
            } else {
                $gender = $request->gender;
            }
        }

        $user = User::create([
            'full_name' => $request->full_name,
            'name' => $request->full_name, // for compatibility
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'contact_number' => $request->contact_number,
            'course' => $role === 'student' ? $request->course : null,
            'year_level' => $role === 'student' ? $request->year_level : null,
            'gender' => $gender,
            'boarding_house_name' => $role === 'landlord' ? $request->boarding_house_name : 'N/A',
            'role' => $role,
        ]);

        // If registering as landlord, create landlord profile
        if ($role === 'landlord') {
            \App\Models\LandlordProfile::create([
                'user_id' => $user->id,
                'contact_number' => $request->contact_number,
                'boarding_house_name' => $request->boarding_house_name,
            ]);
        }
        $user->sendEmailVerificationNotification();

        return redirect()->route('login')->with('success', 'Registration successful. Please verify your email before logging in.');
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
        $roleCounts = User::select('role', DB::raw('COUNT(*) as total'))
            ->groupBy('role')
            ->pluck('total', 'role');

        $totalUsers = User::count();
        $todayNew = User::whereDate('created_at', Carbon::today())->count();
        $last7DaysNew = User::where('created_at', '>=', Carbon::now()->subDays(7))->count();

        // Recent registrations (limit 8)
        $recentUsers = User::orderBy('created_at', 'desc')->limit(8)->get(['full_name','email','role','created_at']);

        // Simple growth percentage (last 7 days vs total)
        $growthPct = $totalUsers > 0 ? round(($last7DaysNew / $totalUsers) * 100, 1) : 0;

        // Basic system status (from configuration)
        $systemStatus = [
            'queue' => (string) config('queue.default', 'sync'),
            'cache' => (string) config('cache.default', 'file'),
            'mail' => (string) config('mail.default', 'log'),
            'version' => app()->version(),
        ];

        // Reports count
        $totalReports = \App\Models\Report::count();
        $pendingReports = \App\Models\Report::where('status', 'pending')->count();

        // Onboarding statistics
        $totalOnboardings = \App\Models\TenantOnboarding::count();
        $pendingOnboardings = \App\Models\TenantOnboarding::where('status', 'pending')->count();
        $completedOnboardings = \App\Models\TenantOnboarding::where('status', 'completed')->count();
        $activeOnboardings = \App\Models\TenantOnboarding::whereNotIn('status', ['completed', 'cancelled'])->count();

        // Properties statistics
        $totalProperties = Property::count();
        $activeProperties = Property::whereHas('rooms')->count();

        // Property approval statistics
        $pendingApprovals = Property::where('approval_status', 'pending')->count();

        // Booking statistics
        $totalBookings = Booking::count();
        $pendingBookings = Booking::where('status', 'pending')->count();
        $approvedBookings = Booking::where('status', 'approved')->count();

        $today = Carbon::today()->toDateString();

        // Active boarded students (approved + currently in stay window)
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

        $boardedByProgram = (clone $activeBoardingsBase)
            ->select(
                DB::raw("COALESCE(NULLIF(TRIM(students.course), ''), 'Not specified') as program"),
                DB::raw('COUNT(DISTINCT bookings.student_id) as total_students')
            )
            ->groupBy('program')
            ->orderByDesc('total_students')
            ->limit(10)
            ->get();

        $activeBoardedStudents = (clone $activeBoardingsBase)
            ->distinct('bookings.student_id')
            ->count('bookings.student_id');

        // Gender analytics with schema-safe fallback if column does not exist.
        $genderCounts = [
            'male' => 0,
            'female' => 0,
            'unspecified' => 0,
        ];

        if (Schema::hasColumn('users', 'gender')) {
            $genderBreakdown = (clone $activeBoardingsBase)
                ->select(
                    DB::raw("LOWER(COALESCE(NULLIF(TRIM(students.gender), ''), 'unspecified')) as gender_key"),
                    DB::raw('COUNT(DISTINCT bookings.student_id) as total_students')
                )
                ->groupBy('gender_key')
                ->pluck('total_students', 'gender_key');

            $genderCounts['male'] = (int) ($genderBreakdown['male'] ?? 0);
            $genderCounts['female'] = (int) ($genderBreakdown['female'] ?? 0);
            $genderCounts['unspecified'] = (int) ($genderBreakdown['unspecified'] ?? 0);

            foreach ($genderBreakdown as $key => $count) {
                if (!in_array($key, ['male', 'female', 'unspecified'], true)) {
                    $genderCounts['unspecified'] += (int) $count;
                }
            }
        } else {
            $genderCounts['unspecified'] = $activeBoardedStudents;
        }

        // Map points for all approved landlord properties with coordinates.
        $landlordMapPoints = Property::query()
            ->with('landlord:id,full_name,email')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->where('approval_status', 'approved')
            ->orderBy('name')
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

        return view('admin.dashboard', compact(
            'roleCounts', 'totalUsers', 'todayNew', 'last7DaysNew', 'growthPct', 'recentUsers', 'systemStatus', 'totalReports', 'pendingReports',
            'totalOnboardings', 'pendingOnboardings', 'completedOnboardings', 'activeOnboardings', 'totalProperties', 'activeProperties',
            'pendingApprovals', 'totalBookings', 'pendingBookings', 'approvedBookings',
            'activeBoardedStudents', 'boardedByBoardingHouse', 'boardedByProgram', 'genderCounts', 'landlordMapPoints'
        ));
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
            ->groupBy('d')
            ->pluck('c', 'd');

        $registrations = array_map(function ($label) use ($userCounts) {
            return (int) ($userCounts[$label] ?? 0);
        }, $labels);

        $approvedByDate = Property::query()
            ->select(DB::raw('DATE(approved_at) as d'), DB::raw('COUNT(*) as c'))
            ->whereNotNull('approved_at')
            ->whereDate('approved_at', '>=', $start)
            ->whereDate('approved_at', '<=', $end)
            ->groupBy('d')
            ->pluck('c', 'd');

        $rejectedByDate = Property::query()
            ->select(DB::raw('DATE(rejected_at) as d'), DB::raw('COUNT(*) as c'))
            ->whereNotNull('rejected_at')
            ->whereDate('rejected_at', '>=', $start)
            ->whereDate('rejected_at', '<=', $end)
            ->groupBy('d')
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
        ]);
    }

    public function adminUsers()
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            abort(403);
        }

        $users = User::with('landlordProfile')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    public function adminUsersByRole(Request $request)
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            abort(403);
        }

        $role = $request->route()->getName() === 'admin.users.students' ? 'student' :
               ($request->route()->getName() === 'admin.users.landlords' ? 'landlord' : 'admin');

        $users = User::with('landlordProfile')
            ->where('role', $role)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

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

        return view('admin.users.by_role', compact('users', 'role', 'roleTitle'));
    }

    public function adminLandlordDetails(User $user)
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            abort(403);
        }

        if ($user->role !== 'landlord') {
            abort(404);
        }

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
                'course' => $booking->student->course,
                'year_level' => $booking->student->year_level,
            ];
        });

        return view('admin.users.landlords.show', compact(
            'user', 'properties', 'totalTenants', 'totalProperties',
            'totalRooms', 'occupiedRooms', 'occupancyRate',
            'pendingOnboarding', 'documentsUploaded', 'contractSigned',
            'depositPaid', 'completedOnboarding', 'totalOnboarding', 'currentTenants'
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

        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'gender' => 'nullable|in:Male,Female,Other',
            'gender_other' => 'nullable|string|max:100',
            'contact_number' => 'nullable|string|max:20',
            'student_id' => 'nullable|string|max:50',
            'course' => 'nullable|string|max:100',
            'year_level' => 'nullable|in:1st Year,2nd Year,3rd Year,4th Year',
            'birth_date' => 'nullable|date',
            'address' => 'nullable|string|max:255',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_number' => 'nullable|string|max:20',
            'emergency_contact_relationship' => 'nullable|string|max:100',
            'guardian_name' => 'nullable|string|max:255',
            'guardian_contact' => 'nullable|string|max:20',
            'parent_contact_number' => 'nullable|string|max:20',
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

    public function adminProperties()
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            abort(403);
        }

        // Get all properties with landlord information and location data
        $properties = Property::with(['landlord', 'rooms'])
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
            ->orderBy('created_at', 'desc')
            ->get();

        // Calculate occupancy rates and add to properties
        $properties->transform(function($property) {
            $property->occupancy_rate = $property->total_rooms > 0
                ? round(($property->occupied_rooms / $property->total_rooms) * 100, 1)
                : 0;
            return $property;
        });

        // Get summary statistics
        $totalProperties = $properties->count();
        $totalRooms = $properties->sum('total_rooms');
        $occupiedRooms = $properties->sum('occupied_rooms');
        $availableRooms = $properties->sum('available_rooms');
        $totalLandlords = $properties->pluck('landlord_id')->unique()->count();

        return view('admin.properties.index', compact(
            'properties', 'totalProperties', 'totalRooms', 'occupiedRooms',
            'availableRooms', 'totalLandlords'
        ));
    }

    public function landlordDashboard()
    {
        if (!Auth::check() || Auth::user()->role !== 'landlord') {
            abort(403);
        }
        $landlordId = Auth::id();

        $properties = Property::where('landlord_id', $landlordId)
            ->withCount([
                'rooms as rooms_total_live',
                'rooms as rooms_vacant_live' => function ($q) {
                    $q->where('status', 'available')->where('slots_available', '>', 0);
                },
            ])
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

        return view('landlord.dashboard', compact(
            'properties','propertiesCount','totalRooms','vacantRooms','pendingRequests','recommendedRooms','unreadMessages','unreadMessagesList','recentReceivedMessages'
        ));
    }

    public function landlordProfile()
    {
        if (!Auth::check() || Auth::user()->role !== 'landlord') {
            abort(403);
        }

        $user = Auth::user();
        return view('landlord.profile.edit', compact('user'));
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
            'contact_number' => 'nullable|string|max:20',
            'boarding_house_name' => 'nullable|string|max:255',
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

        // Update basic info
        $user->update([
            'full_name' => $request->full_name,
            'email' => $request->email,
            'contact_number' => $request->contact_number,
            'boarding_house_name' => $request->boarding_house_name,
        ]);

        if ($request->hasFile('profile_image')) {
            if (!empty($user->profile_image_path)) {
                Storage::disk('public')->delete($user->profile_image_path);
            }

            try {
                $user->profile_image_path = str_replace('\\', '/', $request->file('profile_image')->store('profiles', 'public'));
                $user->save();
            } catch (\Symfony\Component\Mime\Exception\LogicException $e) {
                return back()->withInput()->with('error', 'Unable to process the uploaded image. Please ensure the PHP "fileinfo" extension is enabled and try a smaller image if needed (PHP upload limit).');
            }
        }

        // Update password if provided
        if ($request->filled('new_password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'Current password is incorrect.']);
            }
            $user->update(['password' => Hash::make($request->new_password)]);
        }

        return back()->with('success', 'Profile updated successfully.');
    }

    public function studentDashboard(Request $request)
    {
        if (!Auth::check() || Auth::user()->role !== 'student') {
            abort(403);
        }
        $student = Auth::user();
        $today = now()->toDateString();

        // Treat any non-ended approved booking as "current" (even if check-in is in the future)
        $currentApprovedBooking = $student->bookings()
            ->where('status', 'approved')
            ->where('check_out', '>', $today)
            ->with(['room.property.landlord'])
            ->orderByDesc('check_in')
            ->first();
        $hasCurrentApprovedBooking = !empty($currentApprovedBooking);

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
                $q->where('approval_status', 'approved');
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
                $q->where('approval_status', 'approved');
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
            ->where('approval_status', 'approved')
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
            'recentBookings','recentMessages','latestOnboarding','allOnboardings','recentReports',
            'messageProperties','messageThreads',
            'currentApprovedBooking','hasCurrentApprovedBooking',
            'leaveRequests','currentBookingLeaveRequests',
            'roommates','roommatesCount','roomCapacity'
        ));
    }
}