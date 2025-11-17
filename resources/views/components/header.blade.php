<header class="header">
    <nav class="navbar">
        <div class="container">
            <div class="nav-wrapper">
                <!-- Logo -->
                <a href="{{ url('/') }}" class="logo">
                    <i class="fas fa-user-clock"></i>
                    <span>Sistem Informasi Absensi Perusahaan</span>
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
                    <li><a href="{{ url('/#about') }}" class="nav-link">Tentang Sistem</a></li>
                    <li><a href="{{ url('/#services') }}" class="nav-link">Fitur</a></li>
                    <li><a href="{{ url('/#portfolio') }}" class="nav-link">Dashboard</a></li>
                    <li><a href="{{ url('/#contact') }}" class="nav-link">Support</a></li>
                    <li><a href="{{ route('login') }}" class="btn-login">Login</a></li>
                </ul>
            </div>
        </div>
    </nav>
</header>
