<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Bookings</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container">
    <a class="navbar-brand" href="{{ route('student.dashboard') }}">Student</a>
    <div class="ms-auto">
      <a href="{{ route('student.rooms.index') }}" class="btn btn-sm btn-outline-light me-2">Browse Rooms</a>
      <a href="{{ route('messages.index') }}" class="btn btn-sm btn-outline-light me-2">Messages</a>
      <form class="d-inline" method="POST" action="{{ route('logout') }}">@csrf<button class="btn btn-sm btn-outline-light">Logout</button></form>
    </div>
  </div>
</nav>
<main class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">My Bookings</h1>
    <a href="{{ route('student.dashboard') }}" class="btn btn-outline-secondary">Back</a>
  </div>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  <div class="card shadow-sm">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>Property</th>
            <th>Room</th>
            <th>Dates</th>
            <th>Notes</th>
            <th>Status</th>
            <th>Requested</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($bookings as $b)
            <tr @class(['table-info'=> $loop->first])>
              <td>{{ $b->room->property->name }}<div class="small text-muted">{{ $b->room->property->address }}</div></td>
              <td>{{ $b->room->room_number }}</td>
              <td>{{ $b->check_in->format('M d, Y') }} → {{ $b->check_out->format('M d, Y') }}</td>
              <td class="small">{{ $b->notes ?: '—' }}</td>
              <td>
                @switch($b->status)
                  @case('pending') <span class="badge text-bg-warning">Pending</span> @break
                  @case('approved') <span class="badge text-bg-success">Approved</span> @break
                  @case('rejected') <span class="badge text-bg-danger">Rejected</span> @break
                  @case('cancelled') <span class="badge text-bg-secondary">Cancelled</span> @break
                  @default <span class="badge text-bg-secondary">{{ ucfirst($b->status) }}</span>
                @endswitch
                @if($loop->first)
                  <span class="badge text-bg-primary ms-1">Latest</span>
                @endif
              </td>
              <td class="small text-muted">{{ $b->created_at->diffForHumans() }}</td>
              <td class="text-end">
                @if($b->status==='pending')
                  <form action="{{ route('student.bookings.cancel', $b->id) }}" method="POST" onsubmit="return confirm('Cancel this booking?');" class="d-flex gap-2 align-items-center justify-content-end">
                    @csrf
                    <input type="text" name="cancel_reason" class="form-control form-control-sm" placeholder="Reason (optional)" style="max-width: 220px;">
                    <button class="btn btn-sm btn-outline-danger">Cancel</button>
                  </form>
                @else
                  <span class="text-muted small">—</span>
                @endif
              </td>
            </tr>
          @empty
            <tr><td colspan="7" class="text-center text-muted py-4">You have no bookings yet.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
