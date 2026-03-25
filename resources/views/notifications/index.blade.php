@extends($layout)

@section('title', 'Notifications')

@section('content')
    @php
        $isStudentLayout = $layout === 'layouts.student_dashboard';
    @endphp

    <div class="notifications-shell">
        <div class="notifications-hero mb-4">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                <div>
                    <div class="text-uppercase small fw-semibold hero-kicker">Activity Center</div>
                    <h1 class="h3 mb-1">Notifications</h1>
                    <div class="hero-sub small">Keep track of booking updates, admin actions, and system alerts.</div>
                </div>
                <form method="POST" action="{{ route('notifications.read_all') }}">
                    @csrf
                    @if($isStudentLayout)
                        <input type="hidden" name="stay" value="1">
                        <input type="hidden" name="panel" value="notifications">
                    @endif
                    <button class="btn btn-outline-secondary rounded-pill px-3">
                        <i class="bi bi-check2-all me-1"></i> Mark all as read
                    </button>
                </form>
            </div>
        </div>

        <div class="notifications-summary mb-4">
            <div class="notifications-summary-item">
                <div class="notifications-summary-top">
                    <span class="notifications-summary-label">Unread</span>
                    <i class="bi bi-bell-fill"></i>
                </div>
                <div class="notifications-summary-value">{{ (int) $unreadCount }}</div>
            </div>
            <div class="notifications-summary-item">
                <div class="notifications-summary-top">
                    <span class="notifications-summary-label">Total</span>
                    <i class="bi bi-collection"></i>
                </div>
                <div class="notifications-summary-value">{{ (int) ($notifications->total() ?? $notifications->count()) }}</div>
            </div>
            <div class="notifications-summary-item">
                <div class="notifications-summary-top">
                    <span class="notifications-summary-label">Page Items</span>
                    <i class="bi bi-list-task"></i>
                </div>
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
                        <div class="notification-icon-wrap">
                            <span class="notification-icon {{ $isUnread ? 'is-unread' : 'is-read' }}">
                                <i class="bi {{ $isUnread ? 'bi-bell-fill' : 'bi-bell' }}"></i>
                            </span>
                        </div>

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
                            <div class="small text-muted d-flex align-items-center gap-2">
                                <span>{{ optional($n->created_at)->format('M d, Y h:i A') }}</span>
                                <span class="dot-sep"></span>
                                <span>{{ optional($n->created_at)->diffForHumans() }}</span>
                            </div>
                        </div>

                        <div class="notification-item-actions">
                            @if($isUnread)
                                <form method="POST" action="{{ route('notifications.read', $n->id) }}">
                                    @csrf
                                    @if($isStudentLayout)
                                        <input type="hidden" name="stay" value="1">
                                        <input type="hidden" name="panel" value="notifications">
                                    @endif
                                    <button class="btn btn-sm btn-outline-brand rounded-pill px-3">Mark read</button>
                                </form>
                            @endif
                            @if(is_string($url) && $url !== '')
                                <a href="{{ $url }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">Open</a>
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="empty-state py-5">
                        <i class="bi bi-bell-slash"></i>
                        <div class="h6 mb-1">No notifications yet.</div>
                        <div class="text-muted small">You are all caught up for now.</div>
                    </div>
                @endforelse
            </div>

            <div class="mt-3">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
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

    .notifications-hero {
        border: 1px solid rgba(20,83,45,.14);
        border-radius: 1rem;
        background: radial-gradient(500px 220px at 100% 0%, rgba(167,243,208,.35), transparent 60%), linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        padding: .95rem 1rem;
    }

    .hero-kicker {
        letter-spacing: .07em;
        color: rgba(15,23,42,.62);
    }

    .hero-sub {
        color: rgba(15,23,42,.66);
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

    .notifications-summary-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: .18rem;
    }

    .notifications-summary-top i {
        color: rgba(20,83,45,.72);
        font-size: .92rem;
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
        align-items: flex-start;
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

    .notification-icon-wrap {
        flex: 0 0 auto;
    }

    .notification-icon {
        width: 38px;
        height: 38px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid rgba(2,8,20,.12);
        color: #475569;
        background: #f8fafc;
    }

    .notification-icon.is-unread {
        background: rgba(254,226,226,.75);
        border-color: rgba(239,68,68,.24);
        color: #dc2626;
    }

    .notification-item-main {
        min-width: 220px;
        flex: 1;
    }

    .dot-sep {
        width: 4px;
        height: 4px;
        border-radius: 999px;
        background: #94a3b8;
        display: inline-block;
    }

    .notification-item-actions {
        display: inline-flex;
        flex-wrap: wrap;
        gap: .45rem;
        align-items: center;
    }

    .empty-state {
        text-align: center;
        color: rgba(15,23,42,.62);
    }

    .empty-state i {
        font-size: 1.7rem;
        margin-bottom: .55rem;
        color: rgba(100,116,139,.8);
    }

    @media (max-width: 991.98px) {
        .notifications-summary {
            grid-template-columns: 1fr;
        }
        .notifications-shell {
            padding: .95rem;
        }
        .notification-item-actions {
            width: 100%;
        }
    }
</style>
@endpush
