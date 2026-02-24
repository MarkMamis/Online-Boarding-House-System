<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Verified;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\StudentPropertyController;
use App\Http\Controllers\StudentProfileController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\TenantOnboardingController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\AdminBookingController;
use App\Http\Controllers\StudentLeaveRequestController;
use App\Http\Controllers\LandlordLeaveRequestController;
use App\Models\Room;

Route::get('/', function () {
    if (Auth::check()) {
        $role = Auth::user()->role;

        if ($role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        if ($role === 'landlord') {
            return redirect()->route('landlord.dashboard');
        }

        return redirect()->route('student.dashboard');
    }

    $today = now()->toDateString();

    $availableRooms = Room::query()
        ->select('rooms.*')
        ->join('properties', 'properties.id', '=', 'rooms.property_id')
        ->where('properties.approval_status', 'approved')
        ->where('rooms.status', 'available')
        ->whereDoesntHave('bookings', function ($query) use ($today) {
            $query->where('status', 'approved')
                ->where('check_in', '<=', $today)
                ->where('check_out', '>', $today);
        })
        ->with([
            'property:id,name,address,landlord_id,image_path,average_rating,ratings_count',
            'property.landlord:id,full_name',
        ])
        ->orderByDesc('properties.average_rating')
        ->orderByDesc('properties.ratings_count')
        ->orderByDesc('rooms.created_at')
        ->limit(6)
        ->get();

    return view('landing', compact('availableRooms'));
})->name('landing');

// Public room details (from landing page)
Route::get('/rooms/{room}', [RoomController::class, 'publicShow'])->name('rooms.public.show');

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
// Legacy combined registration form (kept for compatibility with existing redirects)
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
// Separate role-specific registration forms
Route::get('/register/student', [AuthController::class, 'showRegisterStudentForm'])->name('register.student');
Route::get('/register/landlord', [AuthController::class, 'showRegisterLandlordForm'])->name('register.landlord');
// Registration submit endpoint (POST). Named for direct form action use if needed.
Route::post('/register', [AuthController::class, 'register'])->name('register.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Email verification
Route::middleware('auth')->group(function () {
    Route::get('/email/verify', function () {
        return view('auth.verify-email');
    })->name('verification.notice');

    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'verification-link-sent');
    })->middleware('throttle:6,1')->name('verification.send');
});

Route::get('/email/verify/{id}/{hash}', function (Request $request, string $id, string $hash) {
    $user = \App\Models\User::findOrFail($id);

    if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
        abort(403);
    }

    if (!$user->hasVerifiedEmail()) {
        $user->markEmailAsVerified();
        event(new Verified($user));
    }

    return redirect()->route('login')->with('success', 'Email verified successfully. You can now log in.');
})->middleware(['signed', 'throttle:6,1'])->name('verification.verify');

// Shared authenticated routes (any logged-in role) - require verified email
Route::middleware(['auth', 'verified'])->group(function () {
    // Messaging (used by multiple roles)
    Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
    Route::post('/messages', [MessageController::class, 'store'])->name('messages.store');
    Route::post('/messages/{message}/read', [MessageController::class, 'read'])->name('messages.read');

    // Notifications (in-app)
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read_all');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');

    // Secure document access (controller should still verify ownership)
    Route::get('/documents/{onboarding}/{filename}', [TenantOnboardingController::class, 'viewDocument'])->name('documents.view');
});

// Admin-only (no email verification required)
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', [AuthController::class, 'adminDashboard'])->name('admin.dashboard');
    Route::get('/admin/dashboard/stats', [AuthController::class, 'adminDashboardStats'])->name('admin.dashboard.stats');

    // Admin user management
    Route::get('/admin/users', [AuthController::class, 'adminUsers'])->name('admin.users.index');
    Route::get('/admin/users/students', [AuthController::class, 'adminUsersByRole'])->name('admin.users.students');
    Route::get('/admin/users/landlords', [AuthController::class, 'adminUsersByRole'])->name('admin.users.landlords');
    Route::get('/admin/users/admins', [AuthController::class, 'adminUsersByRole'])->name('admin.users.admins');
    Route::get('/admin/users/landlords/{user}', [AuthController::class, 'adminLandlordDetails'])->name('admin.users.landlords.show');
    Route::get('/admin/users/students/{user}', [AuthController::class, 'adminStudentDetails'])->name('admin.users.students.show');

    // Admin properties management
    Route::get('/admin/properties', [AuthController::class, 'adminProperties'])->name('admin.properties.index');

    // Admin bookings monitoring
    Route::get('/admin/bookings', [AdminBookingController::class, 'index'])->name('admin.bookings.index');

    // Property approval workflow
    Route::get('/admin/properties/pending', [PropertyController::class, 'adminPending'])->name('admin.properties.pending');
    Route::post('/admin/properties/{property}/approve', [PropertyController::class, 'adminApprove'])->name('admin.properties.approve');
    Route::post('/admin/properties/{property}/reject', [PropertyController::class, 'adminReject'])->name('admin.properties.reject');

    // Admin reports management
    Route::get('/admin/reports', [ReportController::class, 'index'])->name('admin.reports.index');
    Route::get('/admin/reports/{report}', [ReportController::class, 'show'])->name('admin.reports.show');
    Route::put('/admin/reports/{report}', [ReportController::class, 'update'])->name('admin.reports.update');

    // Admin onboarding management
    Route::get('/admin/onboardings', [TenantOnboardingController::class, 'adminIndex'])->name('admin.onboardings.index');
    Route::get('/admin/onboardings/active', [TenantOnboardingController::class, 'adminActive'])->name('admin.onboardings.active');
    Route::get('/admin/onboardings/pending', [TenantOnboardingController::class, 'adminPending'])->name('admin.onboardings.pending');
    Route::get('/admin/onboardings/completed', [TenantOnboardingController::class, 'adminCompleted'])->name('admin.onboardings.completed');
    Route::get('/admin/onboardings/{onboarding}', [TenantOnboardingController::class, 'adminShow'])->name('admin.onboardings.show');
    Route::get('/admin/onboardings/{onboarding}/contract', [TenantOnboardingController::class, 'adminViewContract'])->name('admin.onboardings.contract');
});

// Landlord-only (verified)
Route::middleware(['auth', 'verified', 'role:landlord'])->group(function () {
    Route::get('/landlord/dashboard', [AuthController::class, 'landlordDashboard'])->name('landlord.dashboard');

    // Landlord profile management
    Route::get('/landlord/profile', [AuthController::class, 'landlordProfile'])->name('landlord.profile.edit');
    Route::put('/landlord/profile', [AuthController::class, 'updateLandlordProfile'])->name('landlord.profile.update');

    // Landlord property management
    Route::get('/landlord/properties', [PropertyController::class, 'index'])->name('landlord.properties.index');
    Route::get('/landlord/properties/create', [PropertyController::class, 'create'])->name('landlord.properties.create');
    Route::post('/landlord/properties', [PropertyController::class, 'store'])->name('landlord.properties.store');
    Route::get('/landlord/properties/{property}', [PropertyController::class, 'show'])->name('landlord.properties.show');
    Route::get('/landlord/properties/{property}/edit', [PropertyController::class, 'edit'])->name('landlord.properties.edit');
    Route::put('/landlord/properties/{property}', [PropertyController::class, 'update'])->name('landlord.properties.update');
    Route::delete('/landlord/properties/{property}', [PropertyController::class, 'destroy'])->name('landlord.properties.destroy');

    // Landlord room management (nested under property)
    Route::get('/landlord/properties/{property}/rooms', [RoomController::class, 'index'])->name('landlord.properties.rooms.index');
    Route::get('/landlord/properties/{property}/rooms/create', [RoomController::class, 'create'])->name('landlord.properties.rooms.create');
    Route::post('/landlord/properties/{property}/rooms', [RoomController::class, 'store'])->name('landlord.properties.rooms.store');
    Route::get('/landlord/properties/{property}/rooms/{room}/edit', [RoomController::class, 'edit'])->name('landlord.properties.rooms.edit');
    Route::put('/landlord/properties/{property}/rooms/{room}', [RoomController::class, 'update'])->name('landlord.properties.rooms.update');
    Route::delete('/landlord/properties/{property}/rooms/{room}', [RoomController::class, 'destroy'])->name('landlord.properties.rooms.destroy');
    // Quick creation from dashboard (property id passed directly)
    Route::post('/landlord/dashboard/properties/{property}/rooms', [RoomController::class, 'quickStore'])->name('landlord.dashboard.rooms.quick_store');

    // Landlord bookings review
    Route::get('/landlord/bookings', [BookingController::class, 'landlordIndex'])->name('landlord.bookings.index');
    Route::post('/landlord/bookings/{booking}/approve', [BookingController::class, 'approve'])->name('landlord.bookings.approve');
    Route::post('/landlord/bookings/{booking}/reject', [BookingController::class, 'reject'])->name('landlord.bookings.reject');

    // Landlord tenants (current approved bookings)
    Route::get('/landlord/tenants', [BookingController::class, 'landlordTenants'])->name('landlord.tenants.index');

    // Landlord maintenance management
    Route::get('/landlord/maintenance', [MaintenanceController::class, 'index'])->name('landlord.maintenance.index');
    Route::post('/landlord/maintenance/set', [MaintenanceController::class, 'setMaintenance'])->name('landlord.maintenance.set');
    Route::post('/landlord/maintenance/{room}/complete', [MaintenanceController::class, 'completeMaintenance'])->name('landlord.maintenance.complete');

    // Landlord payments & billing
    Route::get('/landlord/payments', [PaymentController::class, 'index'])->name('landlord.payments.index');
    Route::post('/landlord/payments/{booking}/paid', [PaymentController::class, 'markAsPaid'])->name('landlord.payments.mark_paid');
    Route::post('/landlord/payments/{booking}/pending', [PaymentController::class, 'markAsPending'])->name('landlord.payments.mark_pending');

    // Landlord analytics
    Route::get('/landlord/analytics', [AnalyticsController::class, 'index'])->name('landlord.analytics.index');

    // Landlord rooms management
    Route::get('/landlord/rooms', [RoomController::class, 'landlordIndex'])->name('landlord.rooms.index');

    // Landlord messages
    Route::get('/landlord/messages', [MessageController::class, 'index'])->name('landlord.messages.index');

    // Landlord onboarding
    Route::get('/landlord/onboarding', [TenantOnboardingController::class, 'landlordIndex'])->name('landlord.onboarding.index');
    Route::get('/landlord/onboarding/{onboarding}/review', [TenantOnboardingController::class, 'reviewDocuments'])->name('landlord.onboarding.review');
    Route::post('/landlord/onboarding/{onboarding}/approve-documents', [TenantOnboardingController::class, 'approveDocuments'])->name('landlord.onboarding.approve_documents');

    // Landlord leave requests
    Route::get('/landlord/leave-requests', [LandlordLeaveRequestController::class, 'index'])->name('landlord.leave_requests.index');
    Route::post('/landlord/leave-requests/{leaveRequest}/approve', [LandlordLeaveRequestController::class, 'approve'])->name('landlord.leave_requests.approve');
    Route::post('/landlord/leave-requests/{leaveRequest}/reject', [LandlordLeaveRequestController::class, 'reject'])->name('landlord.leave_requests.reject');
});

// Student-only (verified)
Route::middleware(['auth', 'verified', 'role:student'])->group(function () {
    Route::get('/student/dashboard', [AuthController::class, 'studentDashboard'])->name('student.dashboard');

    // Student property map
    Route::get('/student/properties/map-data', [StudentPropertyController::class, 'mapData'])->name('student.properties.map');
    Route::get('/student/properties/map', [StudentPropertyController::class, 'map'])->name('student.properties.map_view');
    Route::get('/student/properties/{property}/rooms-data', [StudentPropertyController::class, 'roomsData'])->name('student.properties.rooms_data');
    Route::get('/student/properties/{property}', [StudentPropertyController::class, 'show'])->name('student.properties.show');

    // Student Profile Management
    Route::get('/student/profile', [StudentProfileController::class, 'show'])->name('student.profile.show');
    Route::get('/student/profile/edit', [StudentProfileController::class, 'edit'])->name('student.profile.edit');
    Route::put('/student/profile', [StudentProfileController::class, 'update'])->name('student.profile.update');
    Route::get('/student/profile/change-password', [StudentProfileController::class, 'editPassword'])->name('student.profile.change-password');
    Route::put('/student/profile/change-password', [StudentProfileController::class, 'updatePassword'])->name('student.profile.update-password');

    // Student room browsing and booking
    Route::get('/student/rooms', [BookingController::class, 'browse'])->name('student.rooms.index');
    Route::get('/student/bookings', [BookingController::class, 'studentIndex'])->name('student.bookings.index');
    Route::get('/rooms/{room}/book', [BookingController::class, 'create'])->name('bookings.create');
    Route::post('/rooms/{room}/book', [BookingController::class, 'store'])->name('bookings.store');
    Route::post('/student/bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('student.bookings.cancel');

    // Student reports
    Route::get('/student/reports/create', [ReportController::class, 'create'])->name('student.reports.create');
    Route::post('/student/reports', [ReportController::class, 'store'])->name('student.reports.store');
    Route::get('/student/reports', [ReportController::class, 'studentIndex'])->name('student.reports.index');
    Route::post('/student/reports/{report}/mark-read', [ReportController::class, 'markResponseRead'])->name('student.reports.mark_read');

    // Student onboarding
    Route::get('/student/onboarding', [TenantOnboardingController::class, 'index'])->name('student.onboarding.index');
    Route::get('/student/onboarding/{onboarding}', [TenantOnboardingController::class, 'show'])->name('student.onboarding.show');
    Route::post('/student/onboarding/{onboarding}/documents', [TenantOnboardingController::class, 'uploadDocuments'])->name('student.onboarding.upload_documents');
    Route::post('/student/onboarding/{onboarding}/sign-contract', [TenantOnboardingController::class, 'signContract'])->name('student.onboarding.sign_contract');
    Route::post('/student/onboarding/{onboarding}/pay-deposit', [TenantOnboardingController::class, 'payDeposit'])->name('student.onboarding.pay_deposit');

    // Student leave requests (onboarded/approved tenants)
    Route::post('/student/leave-requests', [StudentLeaveRequestController::class, 'store'])->name('student.leave_requests.store');
    Route::post('/student/leave-requests/{leaveRequest}/cancel', [StudentLeaveRequestController::class, 'cancel'])->name('student.leave_requests.cancel');
});
