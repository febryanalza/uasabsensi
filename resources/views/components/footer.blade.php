<footer class="footer">
    <div class="footer-top">
        <div class="container">
            <div class="footer-grid">
                <!-- Company Info -->
                <div class="footer-col">
                    <div class="footer-logo">
                        <i class="fas fa-code"></i>
                        <span>PT Pencari Error Sejati</span>
                    </div>
                    <p class="footer-desc">
                        Solusi IT terpercaya untuk bisnis Anda. Kami menghadirkan inovasi teknologi yang mengubah tantangan menjadi peluang.
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
                        <li><a href="{{ url('/#about') }}">Tentang Kami</a></li>
                        <li><a href="{{ url('/#services') }}">Layanan</a></li>
                        <li><a href="{{ url('/#portfolio') }}">Portfolio</a></li>
                        <li><a href="{{ url('/#contact') }}">Kontak</a></li>
                    </ul>
                </div>

                <!-- Services -->
                <div class="footer-col">
                    <h3 class="footer-title">Layanan Kami</h3>
                    <ul class="footer-links">
                        <li><a href="#">Konsultan IT</a></li>
                        <li><a href="#">Pengembangan Software</a></li>
                        <li><a href="#">Cloud Solutions</a></li>
                        <li><a href="#">IT Support</a></li>
                        <li><a href="#">Cyber Security</a></li>
                    </ul>
                </div>

                <!-- Contact Info -->
                <div class="footer-col">
                    <h3 class="footer-title">Hubungi Kami</h3>
                    <ul class="footer-contact">
                        <li>
                            <i class="fas fa-map-marker-alt"></i>
                            <span>Jl. Teknologi No. 123<br>Jakarta Selatan, 12345</span>
                        </li>
                        <li>
                            <i class="fas fa-phone"></i>
                            <span>+62 21 1234 5678</span>
                        </li>
                        <li>
                            <i class="fas fa-envelope"></i>
                            <span>info@pencarierror.com</span>
                        </li>
                        <li>
                            <i class="fas fa-clock"></i>
                            <span>Senin - Jumat: 09:00 - 17:00</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="footer-bottom">
        <div class="container">
            <div class="footer-bottom-content">
                <p>&copy; {{ date('Y') }} PT Pencari Error Sejati. All rights reserved.</p>
                <div class="footer-bottom-links">
                    <a href="#">Privacy Policy</a>
                    <a href="#">Terms of Service</a>
                </div>
            </div>
        </div>
    </div>
</footer>
