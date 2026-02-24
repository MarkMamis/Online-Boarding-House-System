<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Messages</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container">
    <a class="navbar-brand" href="#">Messages</a>
    <div class="ms-auto">
      <a href="{{ auth()->user()->role==='landlord' ? route('landlord.dashboard') : route('student.dashboard') }}" class="btn btn-sm btn-outline-light me-2">Dashboard</a>
      <form class="d-inline" method="POST" action="{{ route('logout') }}">@csrf<button class="btn btn-sm btn-outline-light">Logout</button></form>
    </div>
  </div>
</nav>

<main class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h4 mb-0">Messages</h2>
    <small class="text-muted">Manage tenant communications</small>
  </div>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif
  @if($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
  @endif

  <div class="row g-4">
    <div class="col-md-8">
      <div class="card">
        <div class="card-header">
          <h5 class="mb-0">Recent Messages</h5>
          <small class="text-muted">Showing latest conversations.</small>
        </div>
        <div class="card-body">
          <div class="list-group list-group-flush">
            @forelse($messages as $msg)
              <div class="list-group-item list-group-item-action {{ !$msg->read_at && $msg->receiver_id === $user->id ? 'border-start border-4 border-warning' : '' }}">
                <div class="d-flex justify-content-between align-items-start">
                  <div class="me-3" style="flex: 1 1 auto;">
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
                <p class="mb-0">No messages yet.</p>
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
            @if($user->role==='student')
              @php
                $props = ($messageProperties ?? collect());
                $hasProps = $props->count() > 0;
              @endphp

              <div class="mb-3">
                <label class="form-label">Property</label>
                <select name="property_id" class="form-select" required @disabled(!$hasProps)>
                  @if(!$hasProps)
                    <option value="" selected>No booked properties yet</option>
                  @else
                    <option value="" disabled selected>Select booked property</option>
                    @foreach($props as $p)
                      <option value="{{ $p->id }}" @selected((string)old('property_id') === (string)$p->id)>{{ $p->name }}</option>
                    @endforeach
                  @endif
                </select>
                <div class="form-text">You can message only the owner of a property you booked.</div>
              </div>
            @else
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
            @endif
            <div class="mb-3">
              <label class="form-label">Message</label>
              <textarea name="body" rows="4" class="form-control" required placeholder="Type your message here...">{{ old('body') }}</textarea>
            </div>
            <button class="btn btn-success w-100" type="submit" @if($user->role==='student') @disabled(!$hasProps) @endif>Send Message</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
