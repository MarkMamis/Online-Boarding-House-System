@extends($layout)

@section('title', 'Notifications')

@section('content')
    @if($layout === 'layouts.student_dashboard')
        <div class="notifications-shell">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
                <div>
                    <div class="text-uppercase small text-muted fw-semibold">Activity Center</div>
                    <h1 class="h3 mb-1">Notifications</h1>
                    <div class="text-muted small">Keep track of booking updates and system alerts.</div>
                </div>
                <form method="POST" action="{{ route('notifications.read_all') }}">
                    @csrf
                    <input type="hidden" name="stay" value="1">
                    <input type="hidden" name="panel" value="notifications">
                    <button class="btn btn-outline-secondary rounded-pill px-3">Mark all as read</button>
                </form>
            </div>

            <div class="notifications-summary mb-4">
                <div class="notifications-summary-item">
                    <div class="notifications-summary-label">Unread</div>
                    <div class="notifications-summary-value">{{ (int) $unreadCount }}</div>
                </div>
                <div class="notifications-summary-item">
                    <div class="notifications-summary-label">Total</div>
                    <div class="notifications-summary-value">{{ (int) ($notifications->total() ?? $notifications->count()) }}</div>
                </div>
                <div class="notifications-summary-item">
                    <div class="notifications-summary-label">Page Items</div>
                    <div class="notifications-summary-value">{{ (int) $notifications->count() }}</div>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success rounded-4">{{ session('success') }}</div>
            @endif

            @if(!\Illuminate\Support\Facades\Schema::hasTable('notifications'))
                <div class="alert alert-warning mb-0">
                    Notifications table is not installed yet. Run migrations to enable in-app notifications.
                </div>
            @else
                <div class="notification-list-card">
                    @forelse($notifications as $n)
                        @php
                            $isUnread = is_null($n->read_at);
                            $title = data_get($n->data, 'title', 'Notification');
                            $message = data_get($n->data, 'message', '');
                            $url = data_get($n->data, 'url');
                        @endphp
                        <article class="notification-item {{ $isUnread ? 'is-unread' : '' }}">
                            <div class="notification-item-main">
                                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                    <span class="fw-semibold">{{ $title }}</span>
                                    @if($isUnread)
                                        <span class="badge rounded-pill text-bg-danger">Unread</span>
                                    @else
                                        <span class="badge rounded-pill text-bg-light border">Read</span>
                                    @endif
                                </div>
                                @if($message)
                                    <div class="text-muted small mb-1">{{ $message }}</div>
                                @endif
                                <div class="small text-muted">{{ $n->created_at?->format('M d, Y h:i A') ?? '' }}</div>
                            </div>

                            <div class="notification-item-actions">
                                @if($isUnread)
                                    <form method="POST" action="{{ route('notifications.read', $n->id) }}">
                                        @csrf
                                        <input type="hidden" name="stay" value="1">
                                        <input type="hidden" name="panel" value="notifications">
                                        <button class="btn btn-sm btn-outline-brand rounded-pill px-3">Mark read</button>
                                    </form>
                                @endif
                                @if(is_string($url) && $url !== '')
                                    <a href="{{ $url }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">Open</a>
                                @endif
                            </div>
                        </article>
                    @empty
                        <div class="text-center text-muted py-5">No notifications yet.</div>
                    @endforelse
                </div>

                <div class="mt-3">
                    {{ $notifications->links() }}
                </div>
            @endif
        </div>
    @else
        <div class="notifications-shell">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
                <div>
                    <div class="text-uppercase small text-muted fw-semibold">Activity Center</div>
                    <h1 class="h3 mb-1">Notifications</h1>
                    <div class="text-muted small">Keep track of booking updates and system alerts.</div>
                </div>
                <form method="POST" action="{{ route('notifications.read_all') }}">
                    @csrf
                    <button class="btn btn-outline-secondary rounded-pill px-3">Mark all as read</button>
                </form>
            </div>

            <div class="notifications-summary mb-4">
                <div class="notifications-summary-item">
                    <div class="notifications-summary-label">Unread</div>
                    <div class="notifications-summary-value">{{ (int) $unreadCount }}</div>
                </div>
                <div class="notifications-summary-item">
                    <div class="notifications-summary-label">Total</div>
                    <div class="notifications-summary-value">{{ (int) ($notifications->total() ?? $notifications->count()) }}</div>
                </div>
                <div class="notifications-summary-item">
                    <div class="notifications-summary-label">Page Items</div>
                    <div class="notifications-summary-value">{{ (int) $notifications->count() }}</div>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success rounded-4">{{ session('success') }}</div>
            @endif

            @if(!\Illuminate\Support\Facades\Schema::hasTable('notifications'))
                <div class="alert alert-warning mb-0">
                    Notifications table is not installed yet. Run migrations to enable in-app notifications.
                </div>
            @else
                <div class="notification-list-card">
                    @forelse($notifications as $n)
                        @php
                            $isUnread = is_null($n->read_at);
                            $title = data_get($n->data, 'title', 'Notification');
                            $message = data_get($n->data, 'message', '');
                            $url = data_get($n->data, 'url');
                        @endphp
                        <article class="notification-item {{ $isUnread ? 'is-unread' : '' }}">
                            <div class="notification-item-main">
                                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                    <span class="fw-semibold">{{ $title }}</span>
                                    @if($isUnread)
                                        <span class="badge rounded-pill text-bg-danger">Unread</span>
                                    @else
                                        <span class="badge rounded-pill text-bg-light border">Read</span>
                                    @endif
                                </div>
                                @if($message)
                                    <div class="text-muted small mb-1">{{ $message }}</div>
                                @endif
                                <div class="small text-muted">{{ $n->created_at->format('M d, Y h:i A') }}</div>
                            </div>
                            <div class="notification-item-actions">
                                @if($isUnread)
                                    <form method="POST" action="{{ route('notifications.read', $n->id) }}">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-brand rounded-pill px-3">Mark read</button>
                                    </form>
                                @endif
                                @if(is_string($url) && $url !== '')
                                    <a href="{{ $url }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">Open</a>
                                @endif
                            </div>
                        </article>
                    @empty
                        <div class="text-center text-muted py-5">No notifications yet.</div>
                    @endforelse
                </div>

                <div class="mt-3">
                    {{ $notifications->links() }}
                </div>
            @endif
        </div>
    @endif
@endsection

@push('styles')
<style>
    .notifications-shell {
        background: linear-gradient(180deg, #ffffff 0%, #fbfdfc 100%);
        border: 1px solid rgba(2,8,20,.08);
        border-radius: 1.25rem;
        box-shadow: 0 10px 26px rgba(2,8,20,.06);
        padding: 1.25rem;
    }
    .notifications-summary {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: .75rem;
    }
    .notifications-summary-item {
        border: 1px solid rgba(20,83,45,.16);
        background: linear-gradient(180deg, rgba(167,243,208,.18), rgba(255,255,255,.85));
        border-radius: .9rem;
        padding: .7rem .8rem;
    }
    .notifications-summary-label {
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: rgba(2,8,20,.55);
        font-weight: 700;
        margin-bottom: .2rem;
    }
    .notifications-summary-value {
        font-size: 1rem;
        font-weight: 700;
        color: #14532d;
    }
    .notification-list-card {
        border: 1px solid rgba(2,8,20,.09);
        border-radius: 1rem;
        background: #ffffff;
        overflow: hidden;
    }
    .notification-item {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        gap: .8rem;
        padding: .9rem 1rem;
        border-bottom: 1px solid rgba(2,8,20,.08);
    }
    .notification-item:last-child {
        border-bottom: none;
    }
    .notification-item.is-unread {
        background: linear-gradient(180deg, rgba(254,242,242,.35), #ffffff);
        border-left: 4px solid #ef4444;
        padding-left: .8rem;
    }
    .notification-item-main {
        min-width: 220px;
        flex: 1;
    }
    .notification-item-actions {
        display: inline-flex;
        flex-wrap: wrap;
        gap: .45rem;
        align-items: center;
    }
    @media (max-width: 991.98px) {
        .notifications-summary {
            grid-template-columns: 1fr;
        }
        .notifications-shell {
            padding: .95rem;
        }
    }
</style>
@endpush
