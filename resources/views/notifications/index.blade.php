@extends($layout)

@section('title', 'Notifications')

@section('content')
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
@endsection
