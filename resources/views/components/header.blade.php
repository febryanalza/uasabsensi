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

                <!-- Navigation Menu -->
                <ul class="nav-menu" id="navMenu">
                    <li><a href="{{ url('/') }}" class="nav-link {{ Request::is('/') ? 'active' : '' }}">Home</a></li>
                    <li><a href="{{ url('/#about') }}" class="nav-link">Tentang Kami</a></li>
                    <li><a href="{{ url('/#services') }}" class="nav-link">Layanan</a></li>
                    <li><a href="{{ url('/#portfolio') }}" class="nav-link">Portfolio</a></li>
                    <li><a href="{{ url('/#team') }}" class="nav-link">Tim</a></li>
                    <li><a href="{{ url('/#contact') }}" class="nav-link">Kontak</a></li>
                    <li><a href="{{ url('/login') }}" class="btn-login">Login</a></li>
                </ul>
            </div>
        </div>
    </nav>
</header>
