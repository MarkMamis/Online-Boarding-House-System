<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tenant Onboarding</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container">
    <a class="navbar-brand" href="{{ route('student.dashboard') }}">Student</a>
    <div class="ms-auto">
      <a href="{{ route('student.rooms.index') }}" class="btn btn-sm btn-outline-light me-2">Browse Rooms</a>
      <a href="{{ route('student.bookings.index') }}" class="btn btn-sm btn-outline-light me-2">My Bookings</a>
      <a href="{{ route('messages.index') }}" class="btn btn-sm btn-outline-light me-2">Messages</a>
      <form class="d-inline" method="POST" action="{{ route('logout') }}">@csrf<button class="btn btn-sm btn-outline-light">Logout</button></form>
    </div>
  </div>
</nav>
<main class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Tenant Onboarding</h1>
    <a href="{{ route('student.dashboard') }}" class="btn btn-outline-secondary">Back to Dashboard</a>
  </div>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  @if($onboardings->isEmpty())
    <div class="card shadow-sm">
      <div class="card-body text-center py-5">
        <h5 class="card-title">No Onboarding Processes</h5>
        <p class="card-text text-muted">You don't have any approved bookings that require onboarding yet.</p>
        <a href="{{ route('student.rooms.index') }}" class="btn btn-primary">Browse Available Rooms</a>
      </div>
    </div>
  @else
    <div class="row">
      @foreach($onboardings as $onboarding)
        <div class="col-md-6 mb-4">
          <div class="card shadow-sm h-100">
            <div class="card-header">
              <h6 class="mb-0">{{ $onboarding->booking->room->property->name }}</h6>
              <small class="text-muted">Room {{ $onboarding->booking->room->room_number }}</small>
            </div>
            <div class="card-body">
              <div class="mb-3">
                <strong>Status:</strong>
                <span class="badge @if($onboarding->status === 'completed') bg-success @elseif($onboarding->status === 'pending') bg-warning @else bg-info @endif">
                  {{ ucfirst(str_replace('_', ' ', $onboarding->status)) }}
                </span>
              </div>

              <div class="mb-3">
                <strong>Lease Period:</strong><br>
                <small>{{ $onboarding->booking->check_in->format('M d, Y') }} - {{ $onboarding->booking->check_out->format('M d, Y') }}</small>
              </div>

              @if($onboarding->deposit_amount)
                <div class="mb-3">
                  <strong>Deposit Required:</strong> ₱{{ number_format($onboarding->deposit_amount, 2) }}
                  @if($onboarding->deposit_paid)
                    <span class="badge bg-success ms-2">Paid</span>
                  @else
                    <span class="badge bg-warning ms-2">Pending</span>
                  @endif
                </div>
              @endif

              @if($onboarding->digital_id)
                <div class="mb-3">
                  <strong>Digital Tenant ID:</strong> {{ $onboarding->digital_id }}
                </div>
              @endif

              <div class="progress mb-3" style="height: 8px;">
                @php
                  $progress = match($onboarding->status) {
                    'pending' => 25,
                    'documents_uploaded' => 50,
                    'contract_signed' => 75,
                    'deposit_paid' => 90,
                    'completed' => 100,
                    default => 0
                  };
                @endphp
                <div class="progress-bar" role="progressbar" style="width: {{ $progress }}%"></div>
              </div>

              <div class="d-grid">
                <a href="{{ route('student.onboarding.show', $onboarding) }}" class="btn btn-primary">
                  @if($onboarding->status === 'completed')
                    View Details
                  @else
                    Continue Onboarding
                  @endif
                </a>
              </div>
            </div>
          </div>
        </div>
      @endforeach
    </div>
  @endif
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>