<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room {{ $room->room_number }} - {{ $room->property->name ?? 'Property' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body.room-show-bg { min-height: 100vh; position: relative; }
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
            background: linear-gradient(180deg, rgba(248,249,250,.35), rgba(248,249,250,.82));
            z-index: 0;
            pointer-events: none;
        }
        nav, main { position: relative; z-index: 1; }

        /* Cover */
        .cover-img { width:100%; height:380px; object-fit:cover; border-radius:1rem; }
        .cover-placeholder {
            height:380px; background:#f1f3f5; border-radius:1rem;
            display:flex; align-items:center; justify-content:center;
            color:#adb5bd; font-size:.95rem;
        }

        /* Detail grid */
        .detail-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(180px,1fr)); gap:.75rem; }
        .detail-thumb { position:relative; border-radius:.75rem; overflow:hidden; cursor:pointer; aspect-ratio:4/3; background:#e9ecef; }
        .detail-thumb img { width:100%; height:100%; object-fit:cover; transition:transform .25s ease; }
        .detail-thumb:hover img { transform:scale(1.05); }
        .detail-thumb .thumb-label {
            position:absolute; bottom:0; left:0; right:0;
            padding:.3rem .6rem;
            background:linear-gradient(transparent,rgba(0,0,0,.55));
            color:#fff; font-size:.75rem; font-weight:600;
        }

        /* Lightbox */
        #lightbox { display:none; }
        #lightbox.active { display:flex; }
        #lightbox {
            position:fixed; inset:0; z-index:2000;
            background:rgba(0,0,0,.88);
            align-items:center; justify-content:center;
            backdrop-filter:blur(4px);
        }
        #lightbox img { max-width:min(92vw,900px); max-height:88vh; border-radius:.75rem; box-shadow:0 24px 64px rgba(0,0,0,.5); object-fit:contain; }
        #lightbox .lb-label {
            position:absolute; bottom:2rem; left:50%; transform:translateX(-50%);
            background:rgba(255,255,255,.12); color:#fff;
            padding:.35rem 1rem; border-radius:2rem; font-size:.85rem; font-weight:500; white-space:nowrap;
        }
        #lightbox .lb-close { position:absolute; top:1.25rem; right:1.5rem; color:#fff; font-size:1.6rem; cursor:pointer; opacity:.8; border:none; background:none; }
        #lightbox .lb-close:hover { opacity:1; }
        #lightbox .lb-nav {
            position:absolute; top:50%; transform:translateY(-50%); color:#fff; font-size:2rem;
            cursor:pointer; opacity:.75; border:none; background:rgba(255,255,255,.1);
            border-radius:50%; width:48px; height:48px; display:flex; align-items:center; justify-content:center; transition:opacity .2s,background .2s;
        }
        #lightbox .lb-nav:hover { opacity:1; background:rgba(255,255,255,.2); }
        #lightbox .lb-prev { left:1.25rem; } #lightbox .lb-next { right:1.25rem; }

        /* Misc */
        .inclusion-badge { display:inline-flex; align-items:center; gap:.35rem; background:#f1f5f9; border:1px solid #e2e8f0; border-radius:2rem; padding:.3rem .75rem; font-size:.82rem; color:#475569; font-weight:500; }
        .stat-block { padding:.75rem; background:#f8fafc; border-radius:.75rem; }
        .stat-block .stat-label { font-size:.72rem; color:#94a3b8; font-weight:600; letter-spacing:.04em; text-transform:uppercase; }
        .stat-block .stat-value { font-size:1.1rem; color:#1e293b; font-weight:700; margin-top:.1rem; }
    </style>
</head>
<body class="room-show-bg">

    <nav class="navbar navbar-expand-lg bg-white border-bottom shadow-sm">
        <div class="container-xl py-2">
            <a class="navbar-brand fw-bold" href="{{ route('landing') }}">OBHS</a>
            <div class="ms-auto">
                <a href="{{ route('landing') }}" class="btn btn-outline-secondary rounded-pill btn-sm px-3">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
            </div>
        </div>
    </nav>

    @php
        $roomImage = $room->image_path;
        $propertyImage = $room->property->image_path ?? null;
        $roomImageExists = !empty($roomImage) && (
            \Illuminate\Support\Facades\Storage::disk('public')->exists($roomImage) ||
            file_exists(public_path('storage/' . ltrim($roomImage, '/')))
        );
        $propertyImageExists = !empty($propertyImage) && (
            \Illuminate\Support\Facades\Storage::disk('public')->exists($propertyImage) ||
            file_exists(public_path('storage/' . ltrim($propertyImage, '/')))
        );
        $img = $roomImageExists ? $roomImage : ($propertyImageExists ? $propertyImage : null);
        $rawRoomNumber = trim((string) ($room->room_number ?? ''));
        $normalizedRoomNumber = preg_replace('/^room\s*[:#-]?\s*/i', '', $rawRoomNumber);
        $displayRoomNumber = $normalizedRoomNumber !== '' ? $normalizedRoomNumber : $rawRoomNumber;

        $inclusions = collect(preg_split('/[\s,;]+/', $room->inclusions ?? ''))->filter()->values();
        $detailPhotos = $room->roomImages ?? collect();

        $statusColors = [
            'available'   => ['bg' => 'text-bg-success', 'icon' => 'bi-check-circle-fill'],
            'occupied'    => ['bg' => 'text-bg-danger',  'icon' => 'bi-x-circle-fill'],
            'maintenance' => ['bg' => 'text-bg-warning', 'icon' => 'bi-tools'],
        ];
        $sc = $statusColors[$room->status] ?? ['bg' => 'text-bg-secondary', 'icon' => 'bi-circle'];
    @endphp

    <main class="container-xl py-4 pb-5">

        {{-- Cover + Info --}}
        <div class="row g-4 align-items-start">

            <div class="col-12 col-lg-6">
                @if(!empty($img))
                    <img src="{{ asset('storage/' . $img) }}" alt="Room cover" class="cover-img shadow">
                @else
                    <div class="cover-placeholder shadow">
                        <span><i class="bi bi-image fs-2 d-block text-center mb-2"></i>No cover photo</span>
                    </div>
                @endif
            </div>

            <div class="col-12 col-lg-6">
                <div class="card border-0 shadow rounded-4">
                    <div class="card-body p-4">

                        <div class="d-flex align-items-start justify-content-between gap-2 mb-3">
                            <div>
                                <h1 class="h3 fw-bold mb-1">Room {{ $displayRoomNumber }}</h1>
                                <div class="text-muted small d-flex align-items-center gap-1">
                                    <i class="bi bi-building"></i> {{ $room->property->name ?? '—' }}
                                </div>
                                <div class="text-muted small d-flex align-items-center gap-1 mt-1">
                                    <i class="bi bi-geo-alt"></i> {{ $room->property->address ?? '—' }}
                                </div>
                            </div>
                            <span class="badge rounded-pill {{ $sc['bg'] }} d-flex align-items-center gap-1 px-3 py-2">
                                <i class="bi {{ $sc['icon'] }}"></i> {{ ucfirst($room->status ?? 'unknown') }}
                            </span>
                        </div>

                        <hr class="my-3">

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <div class="stat-block">
                                    <div class="stat-label">Capacity</div>
                                    <div class="stat-value"><i class="bi bi-people text-primary me-1"></i>{{ (int) $room->capacity }} pax</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-block">
                                    <div class="stat-label">Monthly Rent</div>
                                    <div class="stat-value"><i class="bi bi-cash-coin text-success me-1"></i>₱{{ number_format((float) $room->price, 2) }}</div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="stat-block">
                                    <div class="stat-label">Landlord</div>
                                    <div class="stat-value" style="font-size:.95rem;">
                                        <i class="bi bi-person-circle text-secondary me-1"></i>
                                        {{ $room->property->landlord->full_name ?? 'N/A' }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if($inclusions->isNotEmpty())
                            <div class="mb-3">
                                <div class="text-muted mb-2" style="font-size:.72rem; font-weight:600; letter-spacing:.04em; text-transform:uppercase;">Inclusions</div>
                                <div class="d-flex flex-wrap gap-2">
                                    @php
                                        $inclusionIcons = [
                                            'wifi' => 'bi-wifi', 'aircon' => 'bi-thermometer-snow',
                                            'electric' => 'bi-lightning-charge', 'fan' => 'bi-wind',
                                            'water' => 'bi-droplet', 'parking' => 'bi-p-circle',
                                            'cable' => 'bi-tv', 'laundry' => 'bi-bag',
                                            'kitchen' => 'bi-egg-fried', 'ref' => 'bi-snow2',
                                            'refrigerator' => 'bi-snow2',
                                        ];
                                    @endphp
                                    @foreach($inclusions as $inc)
                                        @php
                                            $key = strtolower($inc);
                                            $icon = collect($inclusionIcons)->first(fn($v,$k) => str_contains($key,$k)) ?? 'bi-check2-circle';
                                        @endphp
                                        <span class="inclusion-badge"><i class="bi {{ $icon }}"></i> {{ $inc }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if($room->status === 'available')
                            @auth
                                @if(auth()->user()->role === 'student')
                                    <a href="{{ route('bookings.create', $room->id) }}"
                                       class="btn btn-success w-100 rounded-pill fw-semibold mt-1">
                                        <i class="bi bi-calendar-check me-1"></i> Book This Room
                                    </a>
                                @endif
                            @else
                                <a href="{{ route('login') }}"
                                   class="btn btn-success w-100 rounded-pill fw-semibold mt-1">
                                    <i class="bi bi-calendar-check me-1"></i> Book This Room
                                </a>
                            @endauth
                        @else
                            <button class="btn btn-secondary w-100 rounded-pill fw-semibold mt-1" disabled>
                                <i class="bi bi-slash-circle me-1"></i> Not Available
                            </button>
                        @endif

                    </div>
                </div>
            </div>
        </div>

        {{-- Detail Photos --}}
        @if($detailPhotos->isNotEmpty())
            <div class="mt-5">
                <h5 class="fw-bold mb-3"><i class="bi bi-images text-primary me-2"></i>Room Photos</h5>
                <div class="detail-grid">
                    @foreach($detailPhotos as $idx => $photo)
                        <div class="detail-thumb"
                             onclick="openLightbox({{ $idx }})"
                             data-src="{{ asset('storage/' . $photo->image_path) }}"
                             data-label="{{ $photo->label ?? '' }}">
                            <img src="{{ asset('storage/' . $photo->image_path) }}"
                                 alt="{{ $photo->label ?? 'Room photo' }}" loading="lazy">
                            @if(!empty($photo->label))
                                <div class="thumb-label">{{ $photo->label }}</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

    </main>

    {{-- Lightbox --}}
    <div id="lightbox" onclick="closeLightbox(event)">
        <button class="lb-close" onclick="closeLb()"><i class="bi bi-x-lg"></i></button>
        <button class="lb-nav lb-prev" onclick="lbNav(-1,event)"><i class="bi bi-chevron-left"></i></button>
        <img id="lbImg" src="" alt="">
        <div id="lbLabel" class="lb-label" style="display:none;"></div>
        <button class="lb-nav lb-next" onclick="lbNav(1,event)"><i class="bi bi-chevron-right"></i></button>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const thumbs = [...document.querySelectorAll('.detail-thumb')];
        let current = 0;
        function openLightbox(idx) {
            current = idx; renderLb();
            document.getElementById('lightbox').classList.add('active');
            document.body.style.overflow = 'hidden';
        }
        function renderLb() {
            const t = thumbs[current];
            document.getElementById('lbImg').src = t.dataset.src;
            const lbl = document.getElementById('lbLabel');
            if (t.dataset.label) { lbl.textContent = t.dataset.label; lbl.style.display = ''; }
            else { lbl.style.display = 'none'; }
        }
        function closeLb() {
            document.getElementById('lightbox').classList.remove('active');
            document.body.style.overflow = '';
        }
        function closeLightbox(e) { if (e.target === document.getElementById('lightbox')) closeLb(); }
        function lbNav(dir, e) { e.stopPropagation(); current = (current + dir + thumbs.length) % thumbs.length; renderLb(); }
        document.addEventListener('keydown', e => {
            const lb = document.getElementById('lightbox');
            if (!lb.classList.contains('active')) return;
            if (e.key === 'Escape') closeLb();
            if (e.key === 'ArrowLeft')  lbNav(-1, { stopPropagation: () => {} });
            if (e.key === 'ArrowRight') lbNav(1,  { stopPropagation: () => {} });
        });
    </script>
</body>
</html>
