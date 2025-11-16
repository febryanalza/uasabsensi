<header class="header">
    <nav class="navbar">
        <div class="container">
            <div class="nav-wrapper">
                <!-- Logo -->
                <a href="{{ url('/') }}" class="logo">
                    <i class="fas fa-code"></i>
                    <span>PT Pencari Error Sejati</span>
                </a>

                <!-- Mobile Menu Toggle -->
                <button class="mobile-menu-toggle" id="mobileMenuToggle">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>

                <!-- Navigation Menu (Simplified) -->
                <ul class="nav-menu" id="navMenu">
                    <li><a href="{{ url('/') }}" class="nav-link">Home</a></li>
                    <li><a href="{{ url('/company#about') }}" class="nav-link">Tentang Kami</a></li>
                    <li><a href="{{ url('/company#services') }}" class="nav-link">Layanan</a></li>
                    <li><a href="{{ url('/company#portfolio') }}" class="nav-link">Portfolio</a></li>
                    <li><a href="{{ url('/company#team') }}" class="nav-link">Tim</a></li>
                    <li><a href="{{ url('/company#contact') }}" class="nav-link">Kontak</a></li>
                    <li><a href="{{ route('login') }}" class="btn-login">Login</a></li>
                </ul>
            </div>
        </div>
    </nav>
</header>
