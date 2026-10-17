<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Role - Online Boarding House System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Bricolage+Grotesque:opsz,wght@12..96,600;12..96,700;12..96,800&display=swap" rel="stylesheet">
    <style>
        :root {
            --brand: #14532d;
            --brand-dark: #166534;
            --premium-emerald: #10b981;
            --premium-emerald-deep: #047857;
            --ink: #0f172a;
            --muted: #64748b;
            --line: #e2e8f0;
            --shell: #f8fafc;
        }

        *, *::before, *::after {
            box-sizing: border-box;
        }

        body {
            font-family: 'Manrope', system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
            color: var(--ink);
            background:
                radial-gradient(800px 380px at -4% -10%, rgba(16, 185, 129, .10), transparent 58%),
                radial-gradient(860px 400px at 110% -16%, rgba(4, 120, 87, .06), transparent 60%),
                var(--shell);
            min-height: 100vh;
            margin: 0;
            display: flex;
            flex-direction: column;
        }

        .role-shell {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem 1rem;
        }

        .role-wrap {
            width: min(640px, 100%);
        }

        .role-topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.25rem;
            padding: 0 .25rem;
        }

        .role-logo {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            text-decoration: none;
            color: var(--brand);
            font-weight: 800;
            font-size: 1.05rem;
            letter-spacing: -.02em;
        }

        .role-logo-badge {
            width: 2rem;
            height: 2rem;
            border-radius: .6rem;
            background: rgba(16, 185, 129, .12);
            color: var(--premium-emerald-deep);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.05rem;
        }

        .role-logout {
            border: 0;
            background: transparent;
            color: var(--muted);
            font-size: .88rem;
            font-weight: 600;
            padding: .35rem .65rem;
            border-radius: .5rem;
            transition: all .18s ease;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: .35rem;
        }

        .role-logout:hover {
            color: #b91c1c;
            background: rgba(239, 68, 68, .08);
        }

        .role-card-shell {
            border: 1px solid rgba(226, 232, 240, .9);
            border-radius: 1.5rem;
            background: #ffffff;
            box-shadow: 0 20px 45px -12px rgba(15, 23, 42, .08);
            padding: 2.25rem 2rem;
        }

        .role-kicker {
            font-size: .75rem;
            font-weight: 800;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: var(--premium-emerald-deep);
            margin-bottom: .5rem;
        }

        .role-title {
            font-family: 'Bricolage Grotesque', 'Manrope', system-ui, sans-serif;
            font-size: clamp(1.65rem, 3.2vw, 2.1rem);
            font-weight: 800;
            letter-spacing: -.025em;
            line-height: 1.15;
            color: var(--ink);
            margin-bottom: .4rem;
        }

        .role-subtitle {
            font-size: .95rem;
            color: var(--muted);
            margin-bottom: 1.75rem;
            line-height: 1.5;
        }

        /* Choice Cards Grid */
        .role-choices-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: .85rem;
            margin-bottom: 1.75rem;
        }

        .role-choice-card {
            position: relative;
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            padding: 1.25rem 1.15rem;
            border-radius: 1rem;
            border: 1.5px solid var(--line);
            background: #ffffff;
            cursor: pointer;
            transition: all .2s ease;
            user-select: none;
        }

        .role-choice-card:hover {
            border-color: #cbd5e1;
            background: #f8fafc;
            transform: translateY(-1px);
        }

        .role-choice-card.is-selected {
            border-color: var(--premium-emerald-deep);
            background: rgba(16, 185, 129, .04);
            box-shadow: 0 0 0 1px var(--premium-emerald-deep), 0 8px 20px -6px rgba(4, 120, 87, .12);
        }

        .role-choice-radio {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .role-icon-box {
            width: 2.75rem;
            height: 2.75rem;
            border-radius: .75rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #f1f5f9;
            color: #475569;
            font-size: 1.35rem;
            flex-shrink: 0;
            transition: all .2s ease;
        }

        .role-choice-card.is-selected .role-icon-box {
            background: rgba(16, 185, 129, .14);
            color: var(--premium-emerald-deep);
        }

        .role-choice-content {
            flex: 1;
            min-width: 0;
        }

        .role-choice-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .5rem;
            margin-bottom: .25rem;
        }

        .role-choice-name {
            font-size: 1.1rem;
            font-weight: 800;
            color: var(--ink);
            letter-spacing: -.01em;
        }

        .role-choice-badge {
            font-size: .7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
            padding: .2rem .55rem;
            border-radius: 999px;
            background: #f1f5f9;
            color: #475569;
        }

        .role-choice-card.is-selected .role-choice-badge {
            background: rgba(16, 185, 129, .15);
            color: var(--premium-emerald-deep);
        }

        .role-choice-desc {
            font-size: .88rem;
            color: var(--muted);
            line-height: 1.45;
            margin: 0;
        }

        .role-indicator {
            width: 1.35rem;
            height: 1.35rem;
            border-radius: 50%;
            border: 2px solid #cbd5e1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            margin-top: .2rem;
            transition: all .2s ease;
        }

        .role-indicator::after {
            content: "";
            width: .65rem;
            height: .65rem;
            border-radius: 50%;
            background: var(--premium-emerald-deep);
            transform: scale(0);
            transition: transform .18s ease;
        }

        .role-choice-card.is-selected .role-indicator {
            border-color: var(--premium-emerald-deep);
        }

        .role-choice-card.is-selected .role-indicator::after {
            transform: scale(1);
        }

        /* Shared Landing Page Primary Button (.btn-brand) */
        .btn-brand {
            position: relative;
            overflow: hidden;
            isolation: isolate;
            width: 100%;
            padding: .85rem 1.6rem;
            font-weight: 700;
            font-size: 1rem;
            border-radius: 999px;
            border: 1px solid rgba(16, 185, 129, .9);
            background: linear-gradient(125deg, var(--premium-emerald) 0%, var(--premium-emerald-deep) 100%);
            color: #ecfdf5;
            box-shadow: 0 10px 24px rgba(4, 120, 87, .34);
            transform: translateY(0) scale(1);
            transition: transform .22s ease, box-shadow .24s ease, background .22s ease, border-color .22s ease, color .22s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-brand::after {
            content: "";
            position: absolute;
            top: -130%;
            bottom: -130%;
            left: -45%;
            width: 38%;
            background: linear-gradient(120deg, transparent 10%, rgba(255,255,255,.55) 50%, transparent 90%);
            transform: translateX(-190%) rotate(18deg);
            transition: transform .55s ease;
            pointer-events: none;
            z-index: 1;
        }

        .btn-brand:hover:not(:disabled) {
            transform: translateY(-2px) scale(1.01);
            background: linear-gradient(125deg, #0ea770 0%, #046246 100%);
            border-color: #0ea770;
            color: #fff;
            box-shadow: 0 16px 34px rgba(4, 120, 87, .45);
        }

        .btn-brand:hover:not(:disabled)::after {
            transform: translateX(430%) rotate(18deg);
        }

        .btn-brand:active:not(:disabled) {
            transform: translateY(0) scale(.99);
        }

        .btn-brand:focus-visible {
            outline: 2px solid rgba(16, 185, 129, .72);
            outline-offset: 2px;
        }

        .btn-brand:disabled {
            opacity: .5;
            cursor: not-allowed;
            transform: none !important;
            box-shadow: none !important;
        }

        @media (max-width: 575.98px) {
            .role-shell {
                padding: 1rem .75rem;
            }

            .role-card-shell {
                padding: 1.5rem 1.25rem;
                border-radius: 1.25rem;
            }

            .role-choice-card {
                padding: 1rem .9rem;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .role-choice-card,
            .btn-brand,
            .btn-brand::after {
                transition: none !important;
                transform: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="role-shell">
        <div class="role-wrap">
            <header class="role-topbar">
                <a href="{{ url('/') }}" class="role-logo">
                    <span class="role-logo-badge"><i class="bi bi-houses-fill"></i></span>
                    <span>OBHS</span>
                </a>
                <form id="logout-form" method="POST" action="{{ route('logout') }}" class="m-0">
                    @csrf
                    <button type="submit" class="role-logout" title="Sign out of your account">
                        <i class="bi bi-box-arrow-right"></i>
                        <span>Log out</span>
                    </button>
                </form>
            </header>

            <main class="role-card-shell">
                <div class="role-kicker">Role Selection</div>
                <h1 class="role-title">How will you use OBHS?</h1>
                <p class="role-subtitle">Choose the experience that fits your needs.</p>

                @if($errors->any())
                    <div class="alert alert-danger rounded-3 border-0 py-2 px-3 mb-3 small" role="alert">
                        <ul class="mb-0 ps-3">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @php
                    $selectedRole = old('role', $user->role !== 'pending' ? $user->role : 'student');
                @endphp

                <form id="roleForm" method="POST" action="{{ route('onboarding.role.store') }}" class="m-0">
                    @csrf

                    <div class="role-choices-grid" role="radiogroup" aria-label="Account Role">
                        <!-- Student Choice Card -->
                        <label class="role-choice-card {{ $selectedRole === 'student' ? 'is-selected' : '' }}" for="role-student">
                            <input
                                type="radio"
                                id="role-student"
                                name="role"
                                value="student"
                                class="role-choice-radio"
                                {{ $selectedRole === 'student' ? 'checked' : '' }}
                                required
                            >
                            <span class="role-icon-box">
                                <i class="bi bi-mortarboard-fill"></i>
                            </span>
                            <div class="role-choice-content">
                                <div class="role-choice-heading">
                                    <span class="role-choice-name">Student</span>
                                    <span class="role-choice-badge">Looking for Boarding</span>
                                </div>
                                <p class="role-choice-desc">Find verified boarding houses, submit booking requests, and manage your accommodation.</p>
                            </div>
                            <span class="role-indicator" aria-hidden="true"></span>
                        </label>

                        <!-- Landlord Choice Card -->
                        <label class="role-choice-card {{ $selectedRole === 'landlord' ? 'is-selected' : '' }}" for="role-landlord">
                            <input
                                type="radio"
                                id="role-landlord"
                                name="role"
                                value="landlord"
                                class="role-choice-radio"
                                {{ $selectedRole === 'landlord' ? 'checked' : '' }}
                                required
                            >
                            <span class="role-icon-box">
                                <i class="bi bi-house-door-fill"></i>
                            </span>
                            <div class="role-choice-content">
                                <div class="role-choice-heading">
                                    <span class="role-choice-name">Landlord</span>
                                    <span class="role-choice-badge">Property Owner</span>
                                </div>
                                <p class="role-choice-desc">List your boarding house and manage rooms, bookings, and tenants.</p>
                            </div>
                            <span class="role-indicator" aria-hidden="true"></span>
                        </label>
                    </div>

                    <button type="submit" id="submitBtn" class="btn btn-brand">
                        <span>Continue</span>
                        <i class="bi bi-arrow-right"></i>
                    </button>
                </form>
            </main>
        </div>
    </div>

    <script>
        (function () {
            const cards = document.querySelectorAll('.role-choice-card');
            const radios = document.querySelectorAll('.role-choice-radio');

            const syncSelection = function () {
                cards.forEach((card) => {
                    const radio = card.querySelector('.role-choice-radio');
                    if (radio && radio.checked) {
                        card.classList.add('is-selected');
                    } else {
                        card.classList.remove('is-selected');
                    }
                });
            };

            cards.forEach((card) => {
                card.addEventListener('click', function () {
                    const radio = card.querySelector('.role-choice-radio');
                    if (radio) {
                        radio.checked = true;
                        syncSelection();
                    }
                });
            });

            radios.forEach((radio) => {
                radio.addEventListener('change', syncSelection);
            });

            syncSelection();
        })();
    </script>
</body>
</html>
