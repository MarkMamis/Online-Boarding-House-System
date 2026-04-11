<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room {{ $room->room_number }} - {{ $room->property->name ?? 'Property' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&family=Bricolage+Grotesque:opsz,wght@12..96,600;12..96,700&display=swap" rel="stylesheet">
    <style>
        :root {
            --brand: #15803d;
            --brand-dark: #166534;
            --ink: #0f172a;
            --muted: #64748b;
            --paper: #f8fafc;
        }

        body.room-show-bg {
            min-height: 100vh;
            position: relative;
            color: var(--ink);
            font-family: 'Manrope', system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
        }

        h1, h2, h3, h4, h5, .display-font {
            font-family: 'Bricolage Grotesque', 'Manrope', system-ui, sans-serif;
        }
        body.room-show-bg::before {
            content: "";
            position: fixed;
            inset: 0;
            background-image: url("{{ asset('images/MinSU-Calapan.jpg') }}");
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            filter: saturate(1.05) contrast(1.04);
            z-index: 0;
            pointer-events: none;
        }
        body.room-show-bg::after {
            content: "";
            position: fixed;
            inset: 0;
            background:
                radial-gradient(900px circle at 8% 5%, rgba(22, 101, 52, .28), transparent 60%),
                linear-gradient(180deg, rgba(248,250,252,.42), rgba(248,250,252,.84));
            z-index: 0;
            pointer-events: none;
        }
        nav, main { position: relative; z-index: 1; }

        .room-main { padding-top: 7.7rem !important; }

        .navbar-green {
            background: linear-gradient(180deg, #1a5c2e 0%, #2d8a4e 60%, #3aaf65 100%);
            box-shadow: 0 2px 16px rgba(0,0,0,.30);
            overflow: visible !important;
        }

        .navbar-green .nav-link {
            color: rgba(255,255,255,.92);
            font-weight: 600;
            letter-spacing: .01em;
        }

        .navbar-green .nav-link:hover { color: #fff; text-decoration: underline; text-underline-offset: 3px; }

        .navbar-green .btn-link {
            color: rgba(255,255,255,.92) !important;
            font-weight: 600;
        }

        .navbar-green .btn-link:hover {
            color: #fff !important;
        }

        .navbar-green .navbar-toggler {
            border: 0;
            outline: 0;
            box-shadow: none !important;
        }

        .navbar-green .navbar-toggler:focus,
        .navbar-green .navbar-toggler:active {
            border: 0;
            outline: 0;
            box-shadow: none !important;
        }

        .navbar-green .navbar-toggler .hamburger-icon {
            width: 30px;
            height: 22px;
            display: inline-flex;
            flex-direction: column;
            justify-content: center;
            gap: 5px;
        }

        .navbar-green .navbar-toggler .hamburger-icon span {
            display: block;
            height: 2.5px;
            border-radius: 999px;
            background: rgba(255,255,255,.95);
            transform-origin: center;
            transition: transform .28s ease, opacity .2s ease, width .22s ease;
            align-self: flex-end;
        }

        .navbar-green .navbar-toggler .hamburger-icon span:nth-child(1) {
            width: 100%;
        }

        .navbar-green .navbar-toggler .hamburger-icon span:nth-child(2) {
            width: 66.6667%;
        }

        .navbar-green .navbar-toggler .hamburger-icon span:nth-child(3) {
            width: 33.3333%;
        }

        .navbar-green .navbar-toggler[aria-expanded="true"] .hamburger-icon span:nth-child(1) {
            width: 100%;
            align-self: center;
            transform: translateY(7.5px) rotate(45deg);
        }

        .navbar-green .navbar-toggler[aria-expanded="true"] .hamburger-icon span:nth-child(2) {
            opacity: 0;
            width: 0;
        }

        .navbar-green .navbar-toggler[aria-expanded="true"] .hamburger-icon span:nth-child(3) {
            width: 100%;
            align-self: center;
            transform: translateY(-7.5px) rotate(-45deg);
        }

        .navbar-green .navbar-brand {
            position: relative;
            padding-left: 86px;
            margin-left: 0;
            color: #fff;
            font-weight: 700;
        }

        .navbar-brand-text {
            display: inline-flex;
            flex-direction: column;
            justify-content: center;
            align-items: flex-start;
            gap: .12rem;
            line-height: 1;
            vertical-align: middle;
        }

        .navbar-brand-text .brand-line-top {
            font-size: 1.35rem;
            font-weight: 900;
            letter-spacing: .01em;
            text-transform: none;
            color: #ffffff;
            line-height: 1;
        }

        .navbar-brand-text .brand-line-bottom {
            font-size: .86rem;
            font-weight: 700;
            letter-spacing: .03em;
            text-transform: none;
            color: rgba(236, 253, 245, .86);
            line-height: 1;
        }

        .nav-logo-under {
            position: absolute;
            left: 0;
            top: -10px;
            z-index: 2;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .nav-logo-under img {
            height: 84px;
            width: 84px;
            object-fit: contain;
            margin-bottom: -18px;
            filter: drop-shadow(0 5px 10px rgba(0,0,0,.45));
        }

        .room-back-btn {
            border-radius: 999px;
            border-color: rgba(255,255,255,.42);
            color: #fff;
            font-weight: 600;
            background: rgba(255,255,255,.08);
        }

        .room-back-btn:hover {
            background: rgba(255,255,255,.15);
            border-color: rgba(255,255,255,.56);
            color: #fff;
        }

        .section-card {
            border: 1px solid rgba(15, 23, 42, .10);
            background: rgba(255,255,255,.86);
            border-radius: 1rem;
            box-shadow: 0 18px 36px rgba(2, 8, 20, .10);
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
            padding: 1.05rem;
        }

        /* Cover */
        .cover-img {
            width:100%;
            height:410px;
            object-fit:cover;
            border-radius:1.1rem;
            border: 1px solid rgba(15, 23, 42, .09);
            box-shadow: 0 24px 44px rgba(2, 8, 20, .16);
        }
        .cover-placeholder {
            height:410px; background:#f1f3f5; border-radius:1rem;
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
        #lightbox img {
            width: min(92vw, 1200px);
            height: min(88vh, 900px);
            border-radius: .75rem;
            box-shadow: 0 24px 64px rgba(0,0,0,.5);
            object-fit: contain;
        }
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
        .room-info-card {
            border: 1px solid rgba(15, 23, 42, .08);
            background: rgba(255,255,255,.88);
            box-shadow: 0 24px 44px rgba(2, 8, 20, .12);
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
        }

        .inclusion-badge {
            display:inline-flex;
            align-items:center;
            gap:.35rem;
            background:#f1f5f9;
            border:1px solid #e2e8f0;
            border-radius:2rem;
            padding:.32rem .75rem;
            font-size:.82rem;
            color:#475569;
            font-weight:500;
        }

        .stat-block {
            padding:.78rem;
            background:#f8fafc;
            border-radius:.75rem;
            border: 1px solid rgba(148, 163, 184, .20);
        }

        .stat-block .stat-label { font-size:.72rem; color:#94a3b8; font-weight:600; letter-spacing:.04em; text-transform:uppercase; }
        .stat-block .stat-value { font-size:1.1rem; color:#1e293b; font-weight:700; margin-top:.1rem; }

        .book-btn {
            background: linear-gradient(135deg, var(--brand), var(--brand-dark));
            border: 0;
            box-shadow: 0 12px 24px rgba(22, 101, 52, .26);
        }

        .book-btn:hover {
            filter: brightness(.97);
            transform: translateY(-1px);
        }

        .stars {
            display: inline-flex;
            align-items: center;
            gap: .08rem;
            color: #f59e0b;
        }

        .feedback-item {
            border: 1px solid rgba(148, 163, 184, .26);
            border-radius: .85rem;
            padding: .85rem;
            background: #fff;
        }

        .feedback-meta {
            font-size: .82rem;
            color: var(--muted);
        }

        .feedback-comment {
            margin-top: .45rem;
            color: #334155;
            font-size: .94rem;
        }

        @media (max-width: 991.98px) {
            .navbar-green .navbar-brand {
                padding-left: 84px;
            }

            .navbar-brand-text .brand-line-top {
                font-size: 1.08rem;
            }

            .navbar-brand-text .brand-line-bottom {
                font-size: .7rem;
            }

            #landingNav .navbar-nav {
                margin-left: auto;
                width: fit-content;
                align-items: flex-end;
                text-align: right;
                padding-top: .5rem;
            }

            #landingNav .nav-item {
                width: 100%;
            }

            #landingNav .btn {
                margin-left: auto;
            }

            .nav-logo-under {
                top: -6px;
            }

            .nav-logo-under img {
                height: 56px;
                width: 56px;
                margin-bottom: -8px;
            }

            .room-main { padding-top: 6.7rem !important; }

            .cover-img, .cover-placeholder { height: 300px; }
        }
    </style>
</head>
<body class="room-show-bg">

    <x-public-topnav />

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
        $feedbacks = $room->feedbacks ?? collect();
        $feedbackCount = (int) ($room->feedbacks_count ?? $feedbacks->count());
        $avgRating = $room->feedbacks_avg_rating !== null
            ? round((float) $room->feedbacks_avg_rating, 1)
            : ($feedbackCount > 0 ? round((float) $feedbacks->avg('rating'), 1) : 0);

        $statusColors = [
            'available'   => ['bg' => 'text-bg-success', 'icon' => 'bi-check-circle-fill'],
            'occupied'    => ['bg' => 'text-bg-danger',  'icon' => 'bi-x-circle-fill'],
            'maintenance' => ['bg' => 'text-bg-warning', 'icon' => 'bi-tools'],
        ];
        $sc = $statusColors[$room->status] ?? ['bg' => 'text-bg-secondary', 'icon' => 'bi-circle'];
    @endphp

    <main class="container-xl room-main py-4 pb-5">

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
                <div class="card border-0 shadow rounded-4 room-info-card">
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
                                    <div class="stat-value"><i class="bi bi-cash-coin text-success me-1"></i>PHP {{ number_format((float) $room->price, 2) }}</div>
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
                                       class="btn book-btn text-white w-100 rounded-pill fw-semibold mt-1">
                                        <i class="bi bi-calendar-check me-1"></i> Book This Room
                                    </a>
                                @endif
                            @else
                                <a href="{{ route('login') }}"
                                   class="btn book-btn text-white w-100 rounded-pill fw-semibold mt-1">
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

        <div class="row g-4 mt-1">
            <div class="col-12 col-lg-6">
                <div class="section-card h-100">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                        <h5 class="fw-bold mb-0"><i class="bi bi-images text-success me-2"></i>Room Photos</h5>
                        <span class="badge text-bg-light border">{{ $detailPhotos->count() }} photo{{ $detailPhotos->count() > 1 ? 's' : '' }}</span>
                    </div>
                    <div class="text-muted small mb-3">Tap any photo to open gallery view.</div>

                    @if($detailPhotos->isNotEmpty())
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
                    @else
                        <div class="text-muted">No room photos uploaded yet.</div>
                    @endif
                </div>
            </div>

            <div class="col-12 col-lg-6">
                <div class="section-card h-100">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
                        <div>
                            <h5 class="fw-bold mb-1"><i class="bi bi-chat-left-text text-success me-2"></i>Feedback</h5>
                            <div class="text-muted small">Experiences shared by students who stayed in this room.</div>
                        </div>
                        <div class="text-end">
                            <div class="h5 mb-0">{{ number_format($avgRating, 1) }} <span class="text-muted small">/ 5</span></div>
                            <div class="stars" aria-label="Average rating">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="bi {{ $i <= round($avgRating) ? 'bi-star-fill' : 'bi-star' }}"></i>
                                @endfor
                            </div>
                            <div class="text-muted small">{{ $feedbackCount }} review{{ $feedbackCount === 1 ? '' : 's' }}</div>
                        </div>
                    </div>

                    @if($feedbacks->isNotEmpty())
                        <div class="row g-2">
                            @foreach($feedbacks as $feedback)
                                <div class="col-12">
                                    <div class="feedback-item h-100">
                                        <div class="d-flex justify-content-between align-items-start gap-2">
                                            <div>
                                                <div class="fw-semibold">{{ $feedback->public_name }}</div>
                                                <div class="feedback-meta">{{ optional($feedback->created_at)->format('M d, Y') }} • {{ optional($feedback->created_at)->diffForHumans() }}</div>
                                            </div>
                                            <div class="stars" aria-label="{{ (int) $feedback->rating }} out of 5">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <i class="bi {{ $i <= (int) $feedback->rating ? 'bi-star-fill' : 'bi-star' }}"></i>
                                                @endfor
                                            </div>
                                        </div>
                                        <div class="feedback-comment">{{ $feedback->comment }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-muted">No feedback yet for this room.</div>
                    @endif
                </div>
            </div>
        </div>

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
