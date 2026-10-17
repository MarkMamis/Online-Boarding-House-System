<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recover Account - Mindoro State University OBHS</title>
    <link rel="icon" type="image/png" href="{{ asset('images/MinSU_logo.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --brand-color: #047857;
            --brand-hover: #065f46;
            --brand-rgb: 4, 120, 87;
        }
        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            background: #0b1320;
            color: #f8fafc;
            min-height: 100vh;
        }
        .display-font { font-family: 'Space Grotesk', system-ui, sans-serif; }
        .auth-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            background: radial-gradient(circle at 10% 20%, rgba(4, 120, 87, 0.25) 0%, transparent 40%),
                        radial-gradient(circle at 90% 80%, rgba(4, 120, 87, 0.15) 0%, transparent 40%),
                        #0b1320;
            padding: 2rem 1rem;
        }
        .auth-card {
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 1.25rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            max-width: 480px;
            width: 100%;
        }
        .form-control {
            background: rgba(30, 41, 59, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.12);
            color: #fff;
            padding: 0.75rem 1rem;
            border-radius: 0.75rem;
        }
        .form-control:focus {
            background: rgba(30, 41, 59, 0.95);
            border-color: #10b981;
            color: #fff;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.25);
        }
        .btn-brand {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            border: none;
            color: #fff;
            font-weight: 700;
            padding: 0.8rem 1.5rem;
            border-radius: 999px;
            transition: all 0.2s ease;
        }
        .btn-brand:hover {
            background: linear-gradient(135deg, #047857 0%, #065f46 100%);
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 10px 20px -5px rgba(4, 120, 87, 0.5);
        }
        .brand-logo-strip {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }
        .brand-logo-strip img {
            width: 52px;
            height: 52px;
            object-fit: contain;
        }
    </style>
</head>
<body>
    <div class="auth-wrapper">
        <div class="card auth-card">
            <div class="card-body p-4 p-md-5">
                <div class="brand-logo-strip">
                    <img src="{{ asset('images/MinSU_logo.png') }}" alt="MinSU Logo">
                    <img src="{{ asset('images/OSSE-main.png') }}" alt="OSSE Logo">
                </div>

                <div class="text-center mb-4">
                    <h2 class="display-font fw-bold mb-1">Recover account</h2>
                    <p class="text-muted small mb-0">Enter your registered email address to receive secure recovery instructions.</p>
                </div>

                @if (session('status'))
                    <div class="alert alert-success border-0 py-2 small d-flex align-items-center gap-2 mb-3">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>{{ session('status') }}</span>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger border-0 py-2 small mb-3">
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('password.email') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="email" class="form-label small fw-semibold">Registered Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text bg-transparent border-end-0 border-secondary-subtle text-muted">
                                <i class="bi bi-envelope"></i>
                            </span>
                            <input type="email" id="email" name="email" class="form-control border-start-0" value="{{ old('email', request('email')) }}" placeholder="your.name@minsu.edu.ph" required autofocus>
                        </div>
                    </div>

                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-brand">Send Recovery Link</button>
                    </div>
                </form>

                <div class="text-center mt-3">
                    <a href="{{ route('login') }}" class="text-white text-decoration-none small">
                        <i class="bi bi-arrow-left me-1"></i> Back to sign in
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
