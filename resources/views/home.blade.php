@extends('layouts.app')

@section('title', 'Sistem Informasi Absensi Perusahaan - Kelola Absensi Karyawan')

@section('content')
<!-- Hero Section -->
<section class="hero" id="home">
    <div class="hero-bg"></div>
    <div class="container">
        <div class="hero-content">
            <h1 class="hero-title animate-fade-in">
                Sistem Absensi Modern untuk <span class="gradient-text">Perusahaan Anda</span>
            </h1>
            <p class="hero-subtitle animate-fade-in-delay-1">
                Sistem Informasi Absensi Perusahaan adalah solusi terpercaya untuk mengelola kehadiran karyawan. 
                Kami menyediakan sistem absensi digital modern dengan teknologi RFID dan monitoring real-time.
            </p>
            <div class="hero-buttons animate-fade-in-delay-2">
                <a href="{{ route('login') }}" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt"></i> Login Sistem
                </a>
                <a href="#about" class="btn btn-outline">
                    <i class="fas fa-info-circle"></i> Tentang Sistem
                </a>
            </div>
            <div class="hero-stats animate-fade-in-delay-3">
                <div class="stat-item">
                    <h3>1000+</h3>
                    <p>Karyawan Terdaftar</p>
                </div>
                <div class="stat-item">
                    <h3>50+</h3>
                    <p>Perusahaan</p>
                </div>
                <div class="stat-item">
                    <h3>24/7</h3>
                    <p>Monitoring</p>
                </div>
                <div class="stat-item">
                    <h3>RFID</h3>
                    <p>Technology</p>
                </div>
            </div>
        </div>
    </div>
    <div class="hero-scroll">
        <a href="#about">
            <i class="fas fa-chevron-down"></i>
        </a>
    </div>
</section>

<!-- About Section -->
<section class="about" id="about">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Tentang Kami</h2>
            <p class="section-subtitle">Mengenal Sistem Informasi Absensi Perusahaan Lebih Dekat</p>
        </div>
        <div class="about-content">
            <div class="about-text">
                <h3>Solusi Absensi Digital Terpercaya</h3>
                <p>
                    Sistem Informasi Absensi Perusahaan dikembangkan dengan visi untuk menjadi platform absensi digital terdepan. 
                    Dengan teknologi RFID dan monitoring real-time, kami berkomitmen memberikan solusi pengelolaan kehadiran 
                    karyawan yang akurat dan efisien sesuai kebutuhan perusahaan modern.
                </p>
                <p>
                    Kami memahami bahwa setiap perusahaan memiliki kebijakan absensi yang berbeda. Oleh karena itu, sistem 
                    kami dirancang fleksibel dengan fitur kustomisasi aturan absensi, laporan komprehensif, dan integrasi 
                    dengan sistem payroll untuk mendukung operasional HR yang optimal.
                </p>
                <div class="about-features">
                    <div class="feature-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Teknologi RFID Modern</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Real-time Monitoring</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Laporan Otomatis</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Integrasi Payroll</span>
                    </div>
                </div>
            </div>
            <div class="about-image">
                <div class="about-img-wrapper">
                    <i class="fas fa-user-clock"></i>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Services Section -->
<section class="services" id="services">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Fitur Sistem</h2>
            <p class="section-subtitle">Solusi Absensi Digital Komprehensif untuk Perusahaan Modern</p>
        </div>
        <div class="services-grid">
            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-id-card"></i>
                </div>
                <h3>Absensi RFID</h3>
                <p>Sistem absensi menggunakan teknologi RFID yang akurat dan cepat untuk pencatatan kehadiran karyawan real-time.</p>
                <a href="{{ route('login') }}" class="service-link">Akses Sistem <i class="fas fa-arrow-right"></i></a>
            </div>

            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h3>Laporan Real-time</h3>
                <p>Dashboard monitoring dan laporan kehadiran karyawan secara real-time dengan analisis statistik lengkap.</p>
                <a href="#" class="service-link">Lihat Demo <i class="fas fa-arrow-right"></i></a>
            </div>

            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h3>Manajemen Karyawan</h3>
                <p>Pengelolaan data karyawan terintegrasi dengan sistem absensi untuk kemudahan administrasi HR.</p>
                <a href="#" class="service-link">Kelola Data <i class="fas fa-arrow-right"></i></a>
            </div>

            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-cogs"></i>
                </div>
                <h3>Aturan Fleksibel</h3>
                <p>Konfigurasi aturan absensi yang fleksibel sesuai kebijakan perusahaan dengan sistem toleransi otomatis.</p>
                <a href="#" class="service-link">Atur Kebijakan <i class="fas fa-arrow-right"></i></a>
            </div>

            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-money-check-alt"></i>
                </div>
                <h3>Integrasi Payroll</h3>
                <p>Integrasi seamless dengan sistem penggajian untuk perhitungan otomatis berdasarkan data kehadiran.</p>
                <a href="#" class="service-link">Lihat Fitur <i class="fas fa-arrow-right"></i></a>
            </div>

            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-mobile-alt"></i>
                </div>
                <h3>Mobile Access</h3>
                <p>Akses sistem absensi melalui perangkat mobile untuk monitoring dan pengelolaan dari mana saja.</p>
                <a href="#" class="service-link">Download App <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
    </div>
</section>

<!-- Portfolio Section -->
<section class="portfolio" id="portfolio">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Dashboard</h2>
            <p class="section-subtitle">Antarmuka Sistem yang User-Friendly dan Informatif</p>
        </div>
        <div class="portfolio-grid">
            <div class="portfolio-item">
                <div class="portfolio-image">
                    <i class="fas fa-tachometer-alt"></i>
                </div>
                <div class="portfolio-content">
                    <h3>Dashboard Admin</h3>
                    <p>Monitoring Keseluruhan Sistem</p>
                    <span class="portfolio-tag">Admin Panel</span>
                </div>
            </div>

            <div class="portfolio-item">
                <div class="portfolio-image">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="portfolio-content">
                    <h3>Absensi Karyawan</h3>
                    <p>Interface Scan RFID</p>
                    <span class="portfolio-tag">RFID System</span>
                </div>
            </div>

            <div class="portfolio-item">
                <div class="portfolio-image">
                    <i class="fas fa-chart-bar"></i>
                </div>
                <div class="portfolio-content">
                    <h3>Laporan Kehadiran</h3>
                    <p>Analisis Data Komprehensif</p>
                    <span class="portfolio-tag">Analytics</span>
                </div>
            </div>

            <div class="portfolio-item">
                <div class="portfolio-image">
                    <i class="fas fa-user-cog"></i>
                </div>
                <div class="portfolio-content">
                    <h3>Manajemen User</h3>
                    <p>Panel Pengelolaan Karyawan</p>
                    <span class="portfolio-tag">User Management</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Team Section -->
<section class="team" id="team">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Tim Developer</h2>
            <p class="section-subtitle">Pengembang Sistem yang Berpengalaman</p>
        </div>
        <div class="team-grid">
            <div class="team-member">
                <div class="team-image">
                    <i class="fas fa-user-tie"></i>
                </div>
                <h3>Ahmad Rizki</h3>
                <p class="team-position">System Architect</p>
                <div class="team-social">
                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#"><i class="fab fa-github"></i></a>
                </div>
            </div>

            <div class="team-member">
                <div class="team-image">
                    <i class="fas fa-user-tie"></i>
                </div>
                <h3>Dewi Sartika</h3>
                <p class="team-position">Lead Developer</p>
                <div class="team-social">
                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#"><i class="fab fa-github"></i></a>
                </div>
            </div>

            <div class="team-member">
                <div class="team-image">
                    <i class="fas fa-user-tie"></i>
                </div>
                <h3>Budi Hartono</h3>
                <p class="team-position">UI/UX Designer</p>
                <div class="team-social">
                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#"><i class="fab fa-dribbble"></i></a>
                </div>
            </div>

            <div class="team-member">
                <div class="team-image">
                    <i class="fas fa-user-tie"></i>
                </div>
                <h3>Rina Sari</h3>
                <p class="team-position">QA Engineer</p>
                <div class="team-social">
                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#"><i class="fab fa-github"></i></a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contact Section -->
<section class="contact" id="contact">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Support Sistem</h2>
            <p class="section-subtitle">Butuh Bantuan dengan Sistem Absensi? Hubungi Tim Support Kami</p>
        </div>
        <div class="contact-content">
            <div class="contact-info">
                <h3>Informasi Support</h3>
                <p>Tim support kami siap membantu Anda dengan sistem absensi 24/7. Jangan ragu untuk menghubungi kami.</p>
                
                <div class="contact-item">
                    <div class="contact-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div class="contact-text">
                        <h4>Lokasi Server</h4>
                        <p>Data Center Jakarta<br>Cloud Infrastructure</p>
                    </div>
                </div>

                <div class="contact-item">
                    <div class="contact-icon">
                        <i class="fas fa-phone"></i>
                    </div>
                    <div class="contact-text">
                        <h4>Support Hotline</h4>
                        <p>+62 21 1234 5678<br>Emergency: +62 811 1234 567</p>
                    </div>
                </div>

                <div class="contact-item">
                    <div class="contact-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="contact-text">
                        <h4>Email Support</h4>
                        <p>support@absensi-system.com<br>admin@absensi-system.com</p>
                    </div>
                </div>
            </div>

            <div class="contact-form-wrapper">
                <form class="contact-form" action="#" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="name">Nama Lengkap</label>
                        <input type="text" id="name" name="name" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required>
                    </div>

                    <div class="form-group">
                        <label for="phone">Nomor Telepon</label>
                        <input type="tel" id="phone" name="phone" required>
                    </div>

                    <div class="form-group">
                        <label for="subject">Jenis Bantuan</label>
                        <select id="subject" name="subject" required>
                            <option value="">Pilih Jenis Bantuan</option>
                            <option value="technical">Masalah Teknis</option>
                            <option value="rfid">Kartu RFID</option>
                            <option value="report">Laporan Absensi</option>
                            <option value="account">Akun Pengguna</option>
                            <option value="other">Lainnya</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="message">Deskripsi Masalah</label>
                        <textarea id="message" name="message" rows="5" required placeholder="Jelaskan masalah yang Anda alami dengan detail..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-life-ring"></i> Kirim Permintaan Bantuan
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection
