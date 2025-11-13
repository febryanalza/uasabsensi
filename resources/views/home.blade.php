@extends('layouts.app')

@section('title', 'PT Pencari Error Sejati - Konsultan IT & Jasa IT Profesional')

@section('content')
<!-- Hero Section -->
<section class="hero" id="home">
    <div class="hero-bg"></div>
    <div class="container">
        <div class="hero-content">
            <h1 class="hero-title animate-fade-in">
                Solusi IT Terpercaya untuk <span class="gradient-text">Bisnis Anda</span>
            </h1>
            <p class="hero-subtitle animate-fade-in-delay-1">
                PT Pencari Error Sejati adalah mitra terpercaya dalam transformasi digital bisnis Anda. 
                Kami menyediakan konsultan IT profesional dan layanan teknologi informasi yang komprehensif.
            </p>
            <div class="hero-buttons animate-fade-in-delay-2">
                <a href="#contact" class="btn btn-primary">
                    <i class="fas fa-rocket"></i> Mulai Konsultasi
                </a>
                <a href="#services" class="btn btn-outline">
                    <i class="fas fa-play-circle"></i> Lihat Layanan
                </a>
            </div>
            <div class="hero-stats animate-fade-in-delay-3">
                <div class="stat-item">
                    <h3>500+</h3>
                    <p>Proyek Selesai</p>
                </div>
                <div class="stat-item">
                    <h3>300+</h3>
                    <p>Klien Puas</p>
                </div>
                <div class="stat-item">
                    <h3>10+</h3>
                    <p>Tahun Pengalaman</p>
                </div>
                <div class="stat-item">
                    <h3>50+</h3>
                    <p>Expert Team</p>
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
            <p class="section-subtitle">Mengenal PT Pencari Error Sejati Lebih Dekat</p>
        </div>
        <div class="about-content">
            <div class="about-text">
                <h3>Mitra Digital Transformation Anda</h3>
                <p>
                    PT Pencari Error Sejati didirikan dengan visi untuk menjadi perusahaan konsultan IT terdepan di Indonesia. 
                    Dengan tim profesional yang berpengalaman lebih dari 10 tahun, kami berkomitmen memberikan solusi teknologi 
                    terbaik yang disesuaikan dengan kebutuhan bisnis Anda.
                </p>
                <p>
                    Kami memahami bahwa setiap bisnis memiliki tantangan unik. Oleh karena itu, kami tidak hanya menyediakan 
                    layanan IT standar, tetapi juga menganalisis, merancang, dan mengimplementasikan solusi khusus yang 
                    mendorong pertumbuhan bisnis Anda.
                </p>
                <div class="about-features">
                    <div class="feature-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Tim Profesional Bersertifikat</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Metodologi Agile & Scrum</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Support 24/7</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Garansi Kepuasan Klien</span>
                    </div>
                </div>
            </div>
            <div class="about-image">
                <div class="about-img-wrapper">
                    <i class="fas fa-laptop-code"></i>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Services Section -->
<section class="services" id="services">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Layanan Kami</h2>
            <p class="section-subtitle">Solusi IT Komprehensif untuk Semua Kebutuhan Bisnis</p>
        </div>
        <div class="services-grid">
            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-users-cog"></i>
                </div>
                <h3>Konsultan IT</h3>
                <p>Konsultasi strategis IT untuk mengoptimalkan infrastruktur teknologi bisnis Anda dan mencapai efisiensi maksimal.</p>
                <a href="#" class="service-link">Pelajari Lebih Lanjut <i class="fas fa-arrow-right"></i></a>
            </div>

            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-code"></i>
                </div>
                <h3>Pengembangan Software</h3>
                <p>Pembuatan aplikasi web, mobile, dan desktop custom sesuai kebutuhan bisnis dengan teknologi terkini.</p>
                <a href="#" class="service-link">Pelajari Lebih Lanjut <i class="fas fa-arrow-right"></i></a>
            </div>

            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-cloud"></i>
                </div>
                <h3>Cloud Solutions</h3>
                <p>Migrasi dan pengelolaan infrastruktur cloud untuk skalabilitas dan efisiensi biaya yang lebih baik.</p>
                <a href="#" class="service-link">Pelajari Lebih Lanjut <i class="fas fa-arrow-right"></i></a>
            </div>

            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-headset"></i>
                </div>
                <h3>IT Support & Maintenance</h3>
                <p>Dukungan teknis 24/7 dan pemeliharaan sistem untuk memastikan operasional bisnis berjalan lancar.</p>
                <a href="#" class="service-link">Pelajari Lebih Lanjut <i class="fas fa-arrow-right"></i></a>
            </div>

            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h3>Cyber Security</h3>
                <p>Perlindungan sistem dan data bisnis dari ancaman cyber dengan solusi keamanan tingkat enterprise.</p>
                <a href="#" class="service-link">Pelajari Lebih Lanjut <i class="fas fa-arrow-right"></i></a>
            </div>

            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-database"></i>
                </div>
                <h3>Database Management</h3>
                <p>Desain, implementasi, dan optimasi database untuk performa dan keamanan data yang optimal.</p>
                <a href="#" class="service-link">Pelajari Lebih Lanjut <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
    </div>
</section>

<!-- Portfolio Section -->
<section class="portfolio" id="portfolio">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Portfolio</h2>
            <p class="section-subtitle">Proyek-Proyek yang Telah Kami Selesaikan</p>
        </div>
        <div class="portfolio-grid">
            <div class="portfolio-item">
                <div class="portfolio-image">
                    <i class="fas fa-building"></i>
                </div>
                <div class="portfolio-content">
                    <h3>Sistem ERP Manufaktur</h3>
                    <p>PT. Industri Maju Bersama</p>
                    <span class="portfolio-tag">Enterprise Software</span>
                </div>
            </div>

            <div class="portfolio-item">
                <div class="portfolio-image">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="portfolio-content">
                    <h3>E-Commerce Platform</h3>
                    <p>Toko Online Nusantara</p>
                    <span class="portfolio-tag">Web Development</span>
                </div>
            </div>

            <div class="portfolio-item">
                <div class="portfolio-image">
                    <i class="fas fa-mobile-alt"></i>
                </div>
                <div class="portfolio-content">
                    <h3>Mobile Banking App</h3>
                    <p>Bank Digital Indonesia</p>
                    <span class="portfolio-tag">Mobile App</span>
                </div>
            </div>

            <div class="portfolio-item">
                <div class="portfolio-image">
                    <i class="fas fa-hospital"></i>
                </div>
                <div class="portfolio-content">
                    <h3>Hospital Management System</h3>
                    <p>RS. Sehat Sejahtera</p>
                    <span class="portfolio-tag">Healthcare IT</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Team Section -->
<section class="team" id="team">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Tim Kami</h2>
            <p class="section-subtitle">Profesional Berpengalaman di Bidangnya</p>
        </div>
        <div class="team-grid">
            <div class="team-member">
                <div class="team-image">
                    <i class="fas fa-user-tie"></i>
                </div>
                <h3>Budi Santoso</h3>
                <p class="team-position">CEO & Founder</p>
                <div class="team-social">
                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                </div>
            </div>

            <div class="team-member">
                <div class="team-image">
                    <i class="fas fa-user-tie"></i>
                </div>
                <h3>Siti Rahayu</h3>
                <p class="team-position">CTO</p>
                <div class="team-social">
                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                </div>
            </div>

            <div class="team-member">
                <div class="team-image">
                    <i class="fas fa-user-tie"></i>
                </div>
                <h3>Ahmad Fauzi</h3>
                <p class="team-position">Lead Developer</p>
                <div class="team-social">
                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                </div>
            </div>

            <div class="team-member">
                <div class="team-image">
                    <i class="fas fa-user-tie"></i>
                </div>
                <h3>Dewi Kusuma</h3>
                <p class="team-position">Project Manager</p>
                <div class="team-social">
                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contact Section -->
<section class="contact" id="contact">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Hubungi Kami</h2>
            <p class="section-subtitle">Mari Diskusikan Kebutuhan IT Bisnis Anda</p>
        </div>
        <div class="contact-content">
            <div class="contact-info">
                <h3>Informasi Kontak</h3>
                <p>Jangan ragu untuk menghubungi kami. Tim kami siap membantu Anda 24/7.</p>
                
                <div class="contact-item">
                    <div class="contact-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div class="contact-text">
                        <h4>Alamat</h4>
                        <p>Jl. Teknologi No. 123<br>Jakarta Selatan, 12345</p>
                    </div>
                </div>

                <div class="contact-item">
                    <div class="contact-icon">
                        <i class="fas fa-phone"></i>
                    </div>
                    <div class="contact-text">
                        <h4>Telepon</h4>
                        <p>+62 21 1234 5678<br>+62 812 3456 7890</p>
                    </div>
                </div>

                <div class="contact-item">
                    <div class="contact-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="contact-text">
                        <h4>Email</h4>
                        <p>info@pencarierror.com<br>support@pencarierror.com</p>
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
                        <label for="subject">Subjek</label>
                        <input type="text" id="subject" name="subject" required>
                    </div>

                    <div class="form-group">
                        <label for="message">Pesan</label>
                        <textarea id="message" name="message" rows="5" required></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-paper-plane"></i> Kirim Pesan
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection
