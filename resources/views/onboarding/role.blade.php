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
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --brand: #14532d;
            --brand-dark: #166534;
            --ink: #0f172a;
            --muted: #5b6b79;
            --shell: #f8fafc;
            --line: rgba(15, 23, 42, .08);
        }

        body {
            font-family: 'Manrope', system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
            color: var(--ink);
            background:
                radial-gradient(760px 320px at -4% -10%, rgba(34, 197, 94, .14), transparent 58%),
                radial-gradient(860px 340px at 110% -16%, rgba(20, 83, 45, .08), transparent 60%),
                var(--shell);
            min-height: 100vh;
        }

        .role-shell {
            min-height: 100vh;
            padding: 1rem .85rem 1.4rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .role-wrap {
            width: min(760px, 100%);
        }

        .role-topbar {
            display: flex;
            justify-content: flex-end;
            margin-bottom: .8rem;
        }

        .role-logout {
            border: 0;
            background: transparent;
            color: #475569;
            font-size: .92rem;
            font-weight: 600;
            padding: 0;
        }

        .role-logout:hover {
            color: var(--brand);
            text-decoration: underline;
        }

        .role-card-shell {
            border: 1px solid rgba(255, 255, 255, .8);
            border-radius: 1.75rem;
            background: rgba(255, 255, 255, .94);
            box-shadow: 0 24px 54px rgba(15, 23, 42, .12);
            padding: 1.15rem;
            backdrop-filter: blur(10px);
        }

        .role-kicker {
            font-size: .78rem;
            font-weight: 800;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: var(--brand-dark);
            margin-bottom: .45rem;
        }

        .role-title {
            font-size: 1.8rem;
            font-weight: 800;
            letter-spacing: -.02em;
            margin-bottom: .35rem;
        }

        .role-subtitle {
            color: var(--muted);
            margin-bottom: 1.2rem;
        }

        .role-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: .85rem;
        }

        .role-option {
            border: 1px solid var(--line);
            border-radius: 1.25rem;
            background: linear-gradient(180deg, #ffffff, #f8fafc);
            box-shadow: 0 14px 30px rgba(15, 23, 42, .06);
            padding: 1rem;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .role-badge {
            width: 3rem;
            height: 3rem;
            border-radius: .95rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(20, 83, 45, .08);
            color: var(--brand);
            font-size: 1.2rem;
            margin-bottom: .9rem;
        }

        .role-option-title {
            font-size: 1.2rem;
            font-weight: 800;
            margin-bottom: .35rem;
        }

        .role-option-copy {
            color: var(--muted);
            margin-bottom: 1rem;
        }

        .role-submit {
            width: 100%;
            border: 0;
            border-radius: 999px;
            min-height: 46px;
            padding: .8rem 1rem;
            font-weight: 800;
            background: linear-gradient(135deg, #7cf94c, #52d228);
            color: #0f172a;
            box-shadow: 0 14px 28px rgba(82, 210, 40, .22);
        }

        .role-submit:hover {
            filter: brightness(1.02);
        }

        @media (max-width: 767.98px) {
            .role-shell {
                padding: .85rem .72rem 1.1rem;
                align-items: flex-start;
            }

            .role-card-shell {
                border-radius: 1.35rem;
                padding: 1rem;
            }

            .role-title {
                font-size: 1.55rem;
            }

            .role-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="role-shell">
        <div class="role-wrap">
            <div class="role-topbar">
                <form id="logout-form" method="POST" action="{{ route('logout') }}" class="m-0">
                    @csrf
                    <button type="submit" class="role-logout">Logout</button>
                </form>
            </div>

            <section class="role-card-shell">
                <div class="role-kicker">Select Your Role</div>
                <h1 class="role-title">Select your role</h1>
                <p class="role-subtitle">Choose how you want to continue.</p>

                @if($errors->any())
                    <div class="alert alert-danger rounded-4 border-0 shadow-sm">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="role-grid">
                    <form method="POST" action="{{ route('onboarding.role.store') }}" class="m-0">
                        @csrf
                        <input type="hidden" name="role" value="student">
                        <article class="role-option">
                            <div>
                                <span class="role-badge"><i class="bi bi-mortarboard-fill"></i></span>
                                <div class="role-option-title">Student</div>
                                <p class="role-option-copy">Continue to student verification.</p>
                            </div>
                            <button type="submit" class="role-submit">Continue as Student</button>
                        </article>
                    </form>

                    <form method="POST" action="{{ route('onboarding.role.store') }}" class="m-0">
                        @csrf
                        <input type="hidden" name="role" value="landlord">
                        <article class="role-option">
                            <div>
                                <span class="role-badge"><i class="bi bi-house-door-fill"></i></span>
                                <div class="role-option-title">Landlord</div>
                                <p class="role-option-copy">Continue to landlord setup.</p>
                            </div>
                            <button type="submit" class="role-submit">Continue as Landlord</button>
                        </article>
                    </form>
                </div>
            </section>
        </div>
    </div>
</body>
</html>
