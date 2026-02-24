<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room {{ $room->room_number }} - {{ $room->property->name ?? 'Property' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body.room-show-bg {
            min-height: 100vh;
            position: relative;
        }
        body.room-show-bg::before {
            content: "";
            position: fixed;
            inset: 0;
            background-image: url("{{ asset('images/minsu.png') }}");
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            z-index: 0;
            pointer-events: none;
        }
        body.room-show-bg::after {
            content: "";
            position: fixed;
            inset: 0;
            background: linear-gradient(180deg, rgba(248,249,250,.35), rgba(248,249,250,.78));
            z-index: 0;
            pointer-events: none;
        }
        nav, main {
            position: relative;
            z-index: 1;
        }
    </style>
</head>
<body class="room-show-bg">
    <nav class="navbar navbar-expand-lg bg-white border-bottom">
        <div class="container-xl py-2">
            <a class="navbar-brand fw-bold" href="{{ route('landing') }}">OBHS</a>
            <div class="ms-auto">
                <a href="{{ route('landing') }}" class="btn btn-outline-secondary rounded-pill">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
            </div>
        </div>
    </nav> 

    @php
        $img = $room->image_path ?: ($room->property->image_path ?? null);
        $rawRoomNumber = trim((string) ($room->room_number ?? ''));
        $normalizedRoomNumber = preg_replace('/^room\s*[:#-]?\s*/i', '', $rawRoomNumber);
        $displayRoomNumber = $normalizedRoomNumber !== '' ? $normalizedRoomNumber : $rawRoomNumber;
    @endphp

    <main class="container-xl py-4">
        <div class="row g-4">
            <div class="col-12 col-lg-6">
                <div class="card rounded-4 overflow-hidden">
                    @if(!empty($img))
                        <img src="{{ asset('storage/' . $img) }}" alt="Room photo" class="bg-light" style="width: 100%; height: 360px; object-fit: cover;">
                    @else
                        <div class="d-flex align-items-center justify-content-center bg-white" style="height: 360px;">
                            <div class="text-muted">No image available</div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="col-12 col-lg-6">
                <div class="card rounded-4">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-start justify-content-between gap-2">
                            <div>
                                <h1 class="h3 fw-bold mb-1">
                                    <span class="me-1">Room</span>
                                    <span>{{ $displayRoomNumber }}</span>
                                </h1>
                                <div class="text-muted">
                                    <i class="bi bi-building"></i>
                                    {{ $room->property->name ?? 'Property' }}
                                </div>
                                <div class="text-muted">
                                    <i class="bi bi-geo-alt"></i>
                                    {{ $room->property->address ?? '—' }}
                                </div>
                            </div>
                            <span class="badge rounded-pill {{ $room->status === 'available' ? 'text-bg-success' : 'text-bg-secondary' }}">
                                {{ ucfirst($room->status ?? 'unknown') }}
                            </span>
                        </div>

                        <hr>

                        <div class="row g-3">
                            <div class="col-6">
                                <div class="text-muted small">Capacity</div>
                                <div class="fw-semibold"><i class="bi bi-people"></i> {{ (int) $room->capacity }}</div>
                            </div>
                            <div class="col-6">
                                <div class="text-muted small">Price</div>
                                <div class="fw-semibold"><i class="bi bi-cash-coin"></i> ₱{{ number_format((float) $room->price, 2) }}</div>
                            </div>
                            <div class="col-12">
                                <div class="text-muted small">Landlord</div>
                                <div class="fw-semibold"><i class="bi bi-person"></i> {{ $room->property->landlord->full_name ?? 'N/A' }}</div>
                            </div>
                            @if(!empty($room->inclusions))
                                <div class="col-12">
                                    <div class="text-muted small">Inclusions</div>
                                    <div class="text-body">{{ $room->inclusions }}</div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
