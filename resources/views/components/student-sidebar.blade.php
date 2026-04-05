@php
    $notificationsCount = \Illuminate\Support\Facades\Schema::hasTable('notifications')
        ? Auth::user()->unreadNotifications()->count()
        : 0;

    $today = now()->toDateString();
    $tenantOnboarding = \App\Models\TenantOnboarding::where('status', 'completed')
        ->whereHas('booking', function ($q) use ($today) {
            $q->where('student_id', Auth::id())
              ->where('status', 'approved')
              ->where('check_in', '<=', $today)
              ->where('check_out', '>', $today);
        })
        ->with('booking.room')
        ->orderByDesc('updated_at')
        ->first();

    $tenantRoomId = $tenantOnboarding?->booking?->room_id;
    $tenantMode = !empty($tenantRoomId);
    $pendingBookingsCount = isset($pendingBookingsCount)
        ? (int) $pendingBookingsCount
        : \App\Models\Booking::where('student_id', Auth::id())
            ->where('status', 'pending')
            ->count();
    $hasApprovedBooking = \App\Models\Booking::where('student_id', Auth::id())
        ->where('status', 'approved')
        ->exists();
    $showLimitedNavigation = !$tenantMode && !$hasApprovedBooking;
    $hideBrowse = $tenantMode || !empty($hasCurrentApprovedBooking);
    $dashboardRoute = $tenantMode
        ? route('student.tenant.dashboard')
        : route('student.dashboard');
@endphp

<div class="glass-card rounded-4 p-3 sidepanel">
    <div class="d-flex align-items-center gap-2 px-2 py-2 mb-2 sidebar-head">
        @if(!empty(Auth::user()->profile_image_path))
            <img src="{{ asset('storage/' . Auth::user()->profile_image_path) }}" alt="Profile photo" class="rounded-3 border" style="width:40px;height:40px;object-fit:cover;border-color:rgba(22,101,52,.22)!important;">
        @else
            <div class="rounded-3 d-inline-flex align-items-center justify-content-center" style="width:40px;height:40px;background:rgba(22,101,52,.10);border:1px solid rgba(22,101,52,.20);">
                <i class="bi bi-person-badge" style="color:var(--brand);"></i>
            </div>
        @endif
        <div class="min-w-0">
            <div class="fw-semibold text-truncate">{{ Auth::user()->full_name }}</div>
            <div class="small panel-title">Student</div>
        </div>
    </div>

    <div class="list-group list-group-flush rounded-3 overflow-hidden student-nav-list">
        <div class="nav-section">Main</div>
        <a href="{{ $dashboardRoute }}" class="list-group-item {{ request()->routeIs('student.dashboard') || request()->routeIs('student.tenant.dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i>
            <span>Dashboard</span>
        </a>

        <div class="nav-section">Explore</div>
        @if($tenantMode)
            <a href="{{ route('student.rooms.show', $tenantRoomId) }}" class="list-group-item {{ request()->routeIs('student.rooms.show') ? 'active' : '' }}">
                <i class="bi bi-house-door"></i>
                <span>My Room</span>
            </a>
        @endif
        @unless($hideBrowse)
            <a href="{{ route('student.rooms.index') }}" class="list-group-item {{ request()->routeIs('student.rooms.*') ? 'active' : '' }}">
                <i class="bi bi-search"></i>
                <span>Browse Rooms</span>
            </a>
        @endunless
        <a href="{{ route('student.properties.map_view') }}" class="list-group-item {{ request()->routeIs('student.properties.map_view') ? 'active' : '' }}">
            <i class="bi bi-map"></i>
            <span>Property Map</span>
        </a>

        <div class="nav-section">Shortcuts</div>
        <a href="{{ route('notifications.index') }}" class="list-group-item {{ request()->routeIs('notifications.*') ? 'active' : '' }}">
            <i class="bi bi-bell"></i>
            <span>Notifications</span>
            @if($notificationsCount > 0)
                <span class="badge rounded-pill text-bg-danger ms-auto">{{ $notificationsCount }}</span>
            @endif
        </a>

        <div class="nav-section">My Activity</div>
        <a href="{{ route('student.bookings.index') }}" class="list-group-item {{ request()->routeIs('student.bookings.*') ? 'active' : '' }}">
            <i class="bi bi-journal-check"></i>
            <span>Requests</span>
            @if($pendingBookingsCount > 0)
                <span class="badge rounded-pill text-bg-warning ms-auto">{{ $pendingBookingsCount }}</span>
            @endif
        </a>
        <a href="{{ route('messages.index') }}" class="list-group-item {{ request()->routeIs('messages.*') ? 'active' : '' }}">
            <i class="bi bi-chat-dots"></i>
            <span>Messages</span>
            @if(isset($unreadMessagesCount) && $unreadMessagesCount > 0)
                <span class="badge rounded-pill text-bg-danger ms-auto">{{ $unreadMessagesCount }}</span>
            @endif
        </a>
        @if($tenantMode && $tenantRoomId)
            <a href="{{ route('student.rooms.feedback_page', $tenantRoomId) }}" class="list-group-item {{ request()->routeIs('student.rooms.feedback_page') ? 'active' : '' }}">
                <i class="bi bi-star"></i>
                <span>Feedback</span>
            </a>
            <a href="{{ route('student.payments.index') }}" class="list-group-item {{ request()->routeIs('student.payments.*') ? 'active' : '' }}">
                <i class="bi bi-cash-stack"></i>
                <span>Payments</span>
            </a>
        @endif
        @if(!$showLimitedNavigation)
            <a href="{{ route('student.onboarding.index') }}" class="list-group-item {{ request()->routeIs('student.onboarding.*') ? 'active' : '' }}">
                <i class="bi bi-clipboard-check"></i>
                <span>Tenant Onboarding</span>
                    @php
                        $onboardingStatus = $latestOnboarding->status ?? null;
                        $isPending = !empty($latestOnboarding) && ($onboardingStatus !== 'completed');
                    @endphp
                    @if($isPending)
                        <span class="badge rounded-pill text-bg-warning ms-auto">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i>Requires Action
                        </span>
                @endif
            </a>
            @php
                $showRoommatesPanel = !empty($hasCurrentApprovedBooking)
                    && !empty($currentApprovedBooking)
                    && !empty($latestOnboarding)
                    && (int) ($latestOnboarding->booking_id ?? 0) === (int) ($currentApprovedBooking->id ?? 0);
            @endphp
            @if($showRoommatesPanel)
                <a href="{{ route('student.dashboard') }}#roommates" class="list-group-item">
                    <i class="bi bi-people"></i>
                    <span>Roommates</span>
                    @if(isset($roommatesCount) && (int) $roommatesCount > 0)
                        <span class="badge rounded-pill text-bg-light ms-auto">{{ (int) $roommatesCount }}</span>
                    @endif
                </a>
            @endif
        @endif

        <div class="nav-section">Account</div>
        <a href="{{ route('student.profile.show') }}" class="list-group-item {{ request()->routeIs('student.profile.*') ? 'active' : '' }}">
            <i class="bi bi-gear"></i>
            <span>Profile</span>
        </a>

        @if(!$showLimitedNavigation)
            <div class="nav-section">Help</div>
            <a href="{{ route('student.reports.index') }}" class="list-group-item {{ request()->routeIs('student.reports.*') ? 'active' : '' }}">
                <i class="bi bi-question-circle"></i>
                <span>Help Center</span>
                @if(isset($unreadResponsesCount) && $unreadResponsesCount > 0)
                    <span class="badge rounded-pill text-bg-danger ms-auto">{{ $unreadResponsesCount }}</span>
                @endif
            </a>
        @endif

        <div class="nav-section">Session</div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="list-group-item w-100 text-start">
                <i class="bi bi-box-arrow-left"></i>
                <span>Logout</span>
            </button>
        </form>
    </div>
</div>
