<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>500 Internal Server Error | OBHS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&family=Bricolage+Grotesque:opsz,wght@12..96,600;12..96,700&display=swap" rel="stylesheet">
    <style>
        :root {
            --brand: #14532d;
            --brand-2: #166534;
            --brand-rgb: 34, 197, 94;
            --premium-emerald: #10b981;
            --premium-emerald-deep: #047857;
            --ink: #0f172a;
            --paper: #f8fafc;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            font-family: 'Manrope', system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
            color: var(--ink);
            background: var(--paper);
        }

        h1, h2, h3, .display-font {
            font-family: 'Bricolage Grotesque', 'Manrope', system-ui, sans-serif;
        }

        .navbar-green {
            background: linear-gradient(180deg, #1a5c2e 0%, #2d8a4e 60%, #3aaf65 100%);
            box-shadow: 0 2px 16px rgba(0, 0, 0, .3);
        }

        .navbar-brand {
            font-weight: 700;
            color: #ffffff !important;
            letter-spacing: .02em;
        }

        .error-card {
            max-width: 580px;
            margin: 4rem auto;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 2.5rem;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        }

        .error-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.35rem 0.85rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 700;
            background: #fee2e2;
            color: #b91c1c;
            margin-bottom: 1.25rem;
        }

        .btn-brand {
            background: var(--brand);
            color: #ffffff;
            border: none;
            padding: 0.65rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s ease;
        }

        .btn-brand:hover {
            background: var(--brand-2);
            color: #ffffff;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-green px-3">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center gap-2" href="{{ url('/') }}">
                <img src="{{ asset('images/MinSU_logo.png') }}" alt="Logo" width="36" height="36" style="border-radius:50%;object-fit:contain;">
                <span>OBHS</span>
            </a>
        </div>
    </nav>

    <main class="container my-auto">
        <div class="error-card">
            <div class="error-badge">
                <i class="bi bi-exclamation-octagon-fill"></i> Error 500
            </div>
            <h1 class="h3 mb-3 fw-bold">Something Went Wrong</h1>
            <p class="text-muted mb-4">
                We encountered an internal server error while processing your request. Our system administrators have been automatically notified.
            </p>
            <div class="d-flex justify-content-center gap-3">
                <a href="{{ url('/') }}" class="btn-brand">
                    <i class="bi bi-house-door"></i> Return Home
                </a>
                <button type="button" class="btn btn-outline-secondary" onclick="window.location.reload();">
                    <i class="bi bi-arrow-clockwise"></i> Try Again
                </button>
            </div>
        </div>
    </main>

    <footer class="py-3 text-center text-muted small border-top bg-white mt-auto">
        &copy; {{ date('Y') }} Online Boarding House System. All rights reserved.
    </footer>
</body>
</html>
