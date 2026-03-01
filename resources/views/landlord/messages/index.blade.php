@extends('layouts.landlord')

@section('content')
<div class="glass-card rounded-4 p-4 p-md-5">
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h4 mb-0">Messages</h2>
            <small class="text-muted">Manage tenant communications</small>
        </div>

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Recent Messages</h5>
                        <small class="text-muted">Showing latest conversations. <strong>Incoming from students</strong> highlighted below.</small>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            @forelse($messages as $msg)
                                <div class="list-group-item list-group-item-action {{ !$msg->read_at && $msg->receiver_id === $user->id ? 'border-start border-4 border-warning' : '' }}">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="me-3 flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-start mb-1">
                                                <strong>{{ $msg->sender->full_name }} → {{ $msg->receiver->full_name }}</strong>
                                                <small class="text-muted">{{ $msg->created_at->diffForHumans() }}</small>
                                            </div>
                                            <div class="mb-2">{{ $msg->body }}</div>
                                            @if($msg->property)
                                                <div class="small text-muted">Property: {{ $msg->property->name }}</div>
                                            @endif
                                            <div class="mt-2">
                                                @if($msg->read_at)
                                                    <span class="badge bg-success">Read</span>
                                                @elseif($msg->receiver_id === $user->id)
                                                    <span class="badge bg-danger">Unread</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            @if(!$msg->read_at && $msg->receiver_id === $user->id)
                                                <form method="POST" action="{{ route('messages.read', $msg->id) }}" class="d-inline">
                                                    @csrf
                                                    <button class="btn btn-sm btn-outline-success">Mark Read</button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-4 text-muted">
                                    <i class="fas fa-inbox fa-3x mb-3"></i>
                                    <p>No messages yet.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Send Message</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('messages.store') }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">To</label>
                                <select name="receiver_id" class="form-select" required>
                                    <option value="" disabled selected>Select recipient</option>
                                    @foreach($recipients as $r)
                                        <option value="{{ $r->id }}" @selected(old('receiver_id')==$r->id)>{{ $r->full_name }} ({{ $r->role }})</option>
                                    @endforeach
                                </select>
                                <div class="form-text">Based on your recent message interactions.</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Message</label>
                                <textarea name="body" rows="4" class="form-control" required placeholder="Type your message here...">{{ old('body') }}</textarea>
                            </div>
                            <button class="btn btn-brand w-100" type="submit">Send Message</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
@endsection