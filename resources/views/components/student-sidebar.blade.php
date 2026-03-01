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
    $hideBrowse = $tenantMode || !empty($hasCurrentApprovedBooking);
@endphp

<div class="glass-card rounded-4 p-3 sidepanel">
    <div class="d-flex align-items-center gap-2 px-2 pt-2 pb-3">
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

    <div class="px-2 pb-3">
        <div class="input-group input-group-sm">
            <span class="input-group-text bg-white" style="border-color: rgba(2,8,20,.08);"><i class="bi bi-search"></i></span>
            <input id="sidepanelSearch" type="search" class="form-control" placeholder="Search menu…" style="border-color: rgba(2,8,20,.08);">
        </div>
        <div class="d-flex gap-2 mt-2">
            @if($tenantMode)
                <a href="{{ route('student.rooms.show', $tenantRoomId) }}" class="btn btn-brand btn-sm rounded-pill flex-fill">
                    <i class="bi bi-house-door me-1"></i> My Room
                </a>
            @else
                <a href="{{ route('student.rooms.index') }}" class="btn btn-brand btn-sm rounded-pill flex-fill">
                    <i class="bi bi-search me-1"></i> Browse
                </a>
            @endif
            <a href="{{ route('student.bookings.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill flex-fill">
                <i class="bi bi-journal-check me-1"></i> Requests
            </a>
        </div>
    </div>

    <div class="list-group list-group-flush rounded-3 overflow-hidden">
        <div class="nav-section">Main</div>
        <a href="{{ route('student.dashboard') }}" class="list-group-item {{ request()->routeIs('student.dashboard') ? 'active' : '' }}">
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
            @if(isset($pendingBookingsCount) && $pendingBookingsCount > 0)
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
        <a href="{{ route('student.onboarding.index') }}" class="list-group-item {{ request()->routeIs('student.onboarding.*') ? 'active' : '' }}">
            <i class="bi bi-clipboard-check"></i>
            <span>Tenant Onboarding</span>
            @if(!empty($latestOnboarding) && ($latestOnboarding->status ?? '') !== 'completed')
                <span class="badge rounded-pill text-bg-light ms-auto">In progress</span>
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
        <a href="{{ route('student.reports.index') }}" class="list-group-item {{ request()->routeIs('student.reports.*') ? 'active' : '' }}">
            <i class="bi bi-flag"></i>
            <span>My Reports</span>
            @if(isset($unreadResponsesCount) && $unreadResponsesCount > 0)
                <span class="badge rounded-pill text-bg-danger ms-auto">{{ $unreadResponsesCount }}</span>
            @endif
        </a>

        <div class="nav-section">Account</div>
        <a href="{{ route('student.profile.show') }}" class="list-group-item {{ request()->routeIs('student.profile.*') ? 'active' : '' }}">
            <i class="bi bi-gear"></i>
            <span>Profile</span>
        </a>

        <div class="nav-section">Help</div>
        <a href="{{ route('student.reports.create') }}" class="list-group-item {{ request()->routeIs('student.reports.create') ? 'active' : '' }}">
            <i class="bi bi-question-circle"></i>
            <span>Help / Report an issue</span>
        </a>
    </div>
</div>
