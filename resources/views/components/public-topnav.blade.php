<nav class="navbar navbar-expand-lg navbar-green fixed-top">
    <div class="container">
        <a class="navbar-brand text-white fw-bold" href="{{ route('landing') }}">
            <span class="nav-logo-under" aria-hidden="true">
                <img src="{{ asset('images/MinSU_logo.png') }}" alt="MINSU">
            </span>
            <span class="navbar-brand-text">
                <span class="brand-line-top">Mindoro State University</span>
                <span class="brand-line-bottom">Online Boarding House System</span>
            </span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#landingNav" aria-controls="landingNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="hamburger-icon" aria-hidden="true">
                <span></span>
                <span></span>
                <span></span>
            </span>
        </button>
        <div class="collapse navbar-collapse" id="landingNav">
            <ul class="navbar-nav ms-auto gap-lg-3 align-items-lg-center">
                <li class="nav-item"><a class="nav-link" href="{{ route('landing') }}#features">Features</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('landing') }}#students">For Students</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('landing') }}#landlords">For Landlords</a></li>
                <li class="nav-item"><a class="btn btn-link" href="{{ route('login') }}">Log in</a></li>
                <li class="nav-item"><a class="btn btn-outline-light btn-sm rounded-pill px-3" href="{{ route('register') }}">Sign up</a></li>
            </ul>
        </div>
    </div>
</nav>
