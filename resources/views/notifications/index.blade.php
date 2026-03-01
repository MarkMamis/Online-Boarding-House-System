@extends($layout)

@section('title', 'Notifications')

@section('content')
    @if($layout === 'layouts.student_dashboard')
        <div class="glass-card rounded-4 p-4 p-md-5 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h4 class="fw-semibold mb-0">Notifications</h4>
                    <div class="small text-muted">Unread: {{ $unreadCount }}</div>
                </div>
                <div class="d-flex gap-2">
                    <form method="POST" action="{{ route('notifications.read_all') }}">
                        @csrf
                        <input type="hidden" name="stay" value="1">
                        <input type="hidden" name="panel" value="notifications">
                        <button type="submit" class="btn btn-sm btn-outline-secondary rounded-pill">Mark all read</button>
                    </form>
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
                <div class="row g-3">
                    @forelse($notifications as $n)
                        @php
                            $isUnread = is_null($n->read_at);
                            $title = data_get($n->data, 'title', 'Notification');
                            $message = data_get($n->data, 'message', '');
                            $url = data_get($n->data, 'url');
                        @endphp
                        <div class="col-12">
                            <div class="border rounded-4 bg-white shadow-sm p-3">
                                <div class="d-flex justify-content-between align-items-start gap-2">
                                    <div class="min-w-0">
                                        <div class="fw-semibold text-truncate">{{ $title }}</div>
                                        <div class="small text-muted">{{ $n->created_at?->diffForHumans() ?? '' }}</div>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        @if($isUnread)
                                            <span class="badge rounded-pill text-bg-danger">Unread</span>
                                            <form method="POST" action="{{ route('notifications.read', $n->id) }}">
                                                @csrf
                                                <input type="hidden" name="stay" value="1">
                                                <input type="hidden" name="panel" value="notifications">
                                                <button type="submit" class="btn btn-sm btn-outline-secondary rounded-pill">Mark read</button>
                                            </form>
                                        @else
                                            <span class="badge rounded-pill text-bg-light">Read</span>
                                        @endif
                                    </div>
                                </div>

                                @if($message)
                                    <div class="small mt-2">{{ $message }}</div>
                                @endif

                                @if(is_string($url) && $url !== '')
                                    <div class="mt-2">
                                        <a href="{{ $url }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-brand rounded-pill px-3">Open details</a>
                                        <span class="small text-muted ms-2">Opens in a new tab</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="col-12"><div class="alert alert-secondary mb-0">No notifications yet.</div></div>
                    @endforelse
                </div>

                <div class="mt-3">
                    {{ $notifications->links() }}
                </div>
            @endif
        </div>
    @else
        <div class="container py-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
                <div>
                    <h1 class="h4 mb-1">Notifications</h1>
                    <div class="text-muted small">Unread: {{ $unreadCount }}</div>
                </div>
                <div class="d-flex gap-2">
                    <form method="POST" action="{{ route('notifications.read_all') }}">
                        @csrf
                        <button class="btn btn-sm btn-outline-secondary">Mark all as read</button>
                    </form>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if(!\Illuminate\Support\Facades\Schema::hasTable('notifications'))
                <div class="alert alert-warning mb-0">
                    Notifications table is not installed yet. Run migrations to enable in-app notifications.
                </div>
            @else
                <div class="card">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 90px;">Status</th>
                                        <th>Details</th>
                                        <th style="width: 140px;">Date</th>
                                        <th style="width: 140px;" class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($notifications as $n)
                                        @php
                                            $isUnread = is_null($n->read_at);
                                            $title = data_get($n->data, 'title', 'Notification');
                                            $message = data_get($n->data, 'message', '');
                                            $url = data_get($n->data, 'url');
                                        @endphp
                                        <tr class="{{ $isUnread ? 'table-warning' : '' }}">
                                            <td>
                                                @if($isUnread)
                                                    <span class="badge text-bg-danger">Unread</span>
                                                @else
                                                    <span class="badge text-bg-secondary">Read</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="fw-semibold">{{ $title }}</div>
                                                @if($message)
                                                    <div class="text-muted small">{{ $message }}</div>
                                                @endif
                                                @if(is_string($url) && $url !== '')
                                                    <div class="small"><a href="{{ $url }}">Open</a></div>
                                                @endif
                                            </td>
                                            <td class="text-muted small">{{ $n->created_at->format('M d, Y h:i A') }}</td>
                                            <td class="text-end">
                                                @if($isUnread)
                                                    <form method="POST" action="{{ route('notifications.read', $n->id) }}" class="d-inline">
                                                        @csrf
                                                        <button class="btn btn-sm btn-outline-primary">Mark read</button>
                                                    </form>
                                                @else
                                                    <span class="text-muted small">—</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-5">No notifications yet.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="mt-3">
                    {{ $notifications->links() }}
                </div>
            @endif
        </div>
    @endif
@endsection
