<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Email</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root{ --brand:#0ea5a0; --brand-dark:#0b7f7b; }
        body{
            min-height:100vh;
            margin:0;
            background:
                linear-gradient(0deg, rgba(0,0,0,.55), rgba(0,0,0,.55)),
                url("{{ asset('images/minsu.png') }}") center/cover no-repeat;
        }
        .card-shell{
            background: rgba(255,255,255,.10);
            border:1px solid rgba(255,255,255,.20);
            backdrop-filter: blur(10px);
        }
        .btn-brand{ background:var(--brand); border-color:var(--brand); }
        .btn-brand:hover{ background:var(--brand-dark); border-color:var(--brand-dark); }
        .text-white-80{ color: rgba(255,255,255,.80); }
    </style>
</head>
<body class="d-flex align-items-center">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card card-shell text-white shadow-lg">
                    <div class="card-body p-4 p-md-5">
                        <div class="d-flex align-items-start gap-3 mb-3">
                            <div class="rounded-circle d-inline-flex align-items-center justify-content-center" style="width:42px;height:42px;background:rgba(14,165,160,.20);border:1px solid rgba(14,165,160,.45);">
                                <i class="bi bi-envelope-check" style="font-size:20px;color:#bff4f1"></i>
                            </div>
                            <div>
                                <h4 class="mb-1">Verify your email</h4>
                                <div class="text-white-80">
                                    We sent a verification link to your email. Please open it to activate your account.
                                </div>
                            </div>
                        </div>

                        @if (session('status') === 'verification-link-sent')
                            <div class="alert alert-success">A new verification link has been sent to your email.</div>
                        @endif
                        @if (session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif

                        <div class="d-flex flex-column gap-2 mt-4">
                            <form method="POST" action="{{ route('verification.send') }}">
                                @csrf
                                <button type="submit" class="btn btn-brand w-100">
                                    <i class="bi bi-send me-2"></i>Resend verification email
                                </button>
                            </form>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="btn btn-outline-light w-100">
                                    <i class="bi bi-box-arrow-right me-2"></i>Logout
                                </button>
                            </form>

                            <a href="{{ route('login') }}" class="btn btn-link text-white-80 text-decoration-none text-center">
                                Back to Login
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
