<footer class="footer">
    <div class="footer-top">
        <div class="container">
            <div class="footer-grid">
                <!-- Company Info -->
                <div class="footer-col">
                    <div class="footer-logo">
                        <i class="fas fa-user-clock"></i>
                        <span>Sistem Informasi Absensi Perusahaan</span>
                    </div>
                    <p class="footer-desc">
                        Solusi absensi digital terpercaya untuk perusahaan Anda. Kami menghadirkan teknologi RFID modern yang akurat dan efisien.
                    </p>
                    <div class="social-links">
                        <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="footer-col">
                    <h3 class="footer-title">Quick Links</h3>
                    <ul class="footer-links">
                        <li><a href="{{ url('/') }}">Home</a></li>
                        <li><a href="{{ url('/#about') }}">Tentang Sistem</a></li>
                        <li><a href="{{ url('/#services') }}">Fitur</a></li>
                        <li><a href="{{ url('/#portfolio') }}">Dashboard</a></li>
                        <li><a href="{{ url('/#contact') }}">Support</a></li>
                    </ul>
                </div>

                <!-- Services -->
                <div class="footer-col">
                    <h3 class="footer-title">Fitur Sistem</h3>
                    <ul class="footer-links">
                        <li><a href="#">Absensi RFID</a></li>
                        <li><a href="#">Monitoring Real-time</a></li>
                        <li><a href="#">Laporan Kehadiran</a></li>
                        <li><a href="#">Manajemen Karyawan</a></li>
                        <li><a href="#">Integrasi Payroll</a></li>
                    </ul>
                </div>

                <!-- Contact Info -->
                <div class="footer-col">
                    <h3 class="footer-title">Informasi Support</h3>
                    <ul class="footer-contact">
                        <li>
                            <i class="fas fa-server"></i>
                            <span>Data Center Jakarta<br>Cloud Infrastructure</span>
                        </li>
                        <li>
                            <i class="fas fa-phone"></i>
                            <span>+62 21 1234 5678</span>
                        </li>
                        <li>
                            <i class="fas fa-envelope"></i>
                            <span>support@absensi-system.com</span>
                        </li>
                        <li>
                            <i class="fas fa-clock"></i>
                            <span>24/7 System Monitoring</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="footer-bottom">
        <div class="container">
            <div class="footer-bottom-content">
                <p>&copy; {{ date('Y') }} Sistem Informasi Absensi Perusahaan. All rights reserved.</p>
                <div class="footer-bottom-links">
                    <a href="#">Privacy Policy</a>
                    <a href="#">Terms of Service</a>
                </div>
            </div>
        </div>
    </div>
</footer>
