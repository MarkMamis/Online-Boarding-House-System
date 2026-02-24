<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Request Booking</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container">
    <a class="navbar-brand" href="{{ route('student.dashboard') }}">Student</a>
    <div class="ms-auto">
      <form class="d-inline" method="POST" action="{{ route('logout') }}">@csrf<button class="btn btn-sm btn-outline-light">Logout</button></form>
    </div>
  </div>
</nav>
<main class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4">Request Booking</h1>
    <a href="{{ route('student.rooms.index') }}" class="btn btn-outline-secondary">Back</a>
  </div>

  @if($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <div class="card shadow-sm mb-3">
    <div class="card-body">
      <h5 class="card-title mb-1">{{ $room->property->name }}</h5>
      <div class="text-muted small mb-2">{{ $room->property->address }}</div>
      <div class="mb-2"><strong>Room:</strong> {{ $room->room_number }}</div>
      <div class="mb-2"><strong>Capacity:</strong> {{ $room->capacity }}</div>
      <div><strong>Price:</strong> ₱ {{ number_format($room->price, 2) }}</div>
    </div>
  </div>

  <form method="POST" action="{{ route('bookings.store', $room->id) }}" class="card shadow-sm">
    @csrf
    <div class="card-body">
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Check-in</label>
          <input type="date" name="check_in" class="form-control" value="{{ old('check_in') }}" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">Check-out</label>
          <input type="date" name="check_out" class="form-control" value="{{ old('check_out') }}" required>
        </div>
        <div class="col-12">
          <label class="form-label">Notes</label>
          <textarea name="notes" rows="3" class="form-control" placeholder="Any special requests?">{{ old('notes') }}</textarea>
        </div>
      </div>
    </div>
    <div class="card-footer bg-light d-flex justify-content-end">
      <button type="submit" class="btn btn-primary">Submit Request</button>
    </div>
  </form>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
