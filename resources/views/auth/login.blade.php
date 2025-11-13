<!DOCTYPE html>
<html lang="id" class="h-full bg-gray-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - {{ config('app.name') }}</title>

    <!-- Tailwind CSS untuk shared hosting compatibility -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Alpine.js untuk interaktivitas -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Font Awesome untuk icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .login-card {
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        
        .input-focus:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transition: all 0.3s ease;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        
        .floating-animation {
            animation: float 6s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        
        .loading-spinner {
            border: 2px solid #f3f4f6;
            border-top: 2px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>

<body class="h-full" x-data="{ 
    loading: false,
    showPassword: false,
    formData: { email: '', password: '', remember: false },
    errors: {}
}">
    <div class="min-h-full flex">
        <!-- Left side - Background Image/Illustration -->
        <div class="flex-1 hidden lg:block relative overflow-hidden gradient-bg">
            <div class="absolute inset-0 bg-black opacity-20"></div>
            <div class="relative h-full flex items-center justify-center p-8">
                <div class="text-center text-white max-w-md">
                    <!-- Floating Icon -->
                    <div class="mx-auto mb-8 w-24 h-24 bg-white bg-opacity-20 rounded-full flex items-center justify-center floating-animation">
                        <i class="fas fa-building text-4xl text-white"></i>
                    </div>
                    
                    <h2 class="text-3xl font-bold mb-4">
                        Sistem Manajemen HR
                    </h2>
                    <p class="text-lg opacity-90 leading-relaxed">
                        Platform terpadu untuk mengelola karyawan, absensi, gaji, dan aturan perusahaan dengan mudah dan efisien.
                    </p>
                    
                    <!-- Features list -->
                    <div class="mt-8 space-y-3 text-left">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle mr-3 text-green-300"></i>
                            <span>Management Karyawan Terintegrasi</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-check-circle mr-3 text-green-300"></i>
                            <span>Sistem Absensi Otomatis</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-check-circle mr-3 text-green-300"></i>
                            <span>Perhitungan Gaji Real-time</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-check-circle mr-3 text-green-300"></i>
                            <span>Laporan Analytics Lengkap</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right side - Login Form -->
        <div class="flex-1 flex flex-col justify-center py-12 px-4 sm:px-6 lg:flex-none lg:px-20 xl:px-24">
            <div class="mx-auto w-full max-w-sm lg:w-96">
                <!-- Logo and Title -->
                <div class="text-center mb-8">
                    <div class="mx-auto w-16 h-16 bg-gradient-to-r from-blue-600 to-purple-600 rounded-xl flex items-center justify-center mb-4">
                        <i class="fas fa-building text-2xl text-white"></i>
                    </div>
                    <h2 class="text-3xl font-bold text-gray-900">
                        Masuk ke Akun
                    </h2>
                    <p class="mt-2 text-sm text-gray-600">
                        Silakan masuk dengan kredensial Anda
                    </p>
                </div>

                <!-- Login Form -->
                <div class="login-card bg-white rounded-2xl p-8">
                    @if($errors->any())
                    <div class="mb-6 p-4 rounded-lg bg-red-50 border border-red-200">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-circle text-red-400 mr-3"></i>
                            <div>
                                <h3 class="text-sm font-medium text-red-800">Ada kesalahan dalam form:</h3>
                                <ul class="mt-1 text-sm text-red-600 list-disc list-inside">
                                    @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if(session('status'))
                    <div class="mb-6 p-4 rounded-lg bg-green-50 border border-green-200">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-green-400 mr-3"></i>
                            <span class="text-green-800">{{ session('status') }}</span>
                        </div>
                    </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}" x-on:submit="loading = true" id="loginForm">
                        @csrf
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">\n                        
                        <!-- Email -->
                        <div class="mb-6">
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-envelope mr-2 text-gray-400"></i>
                                Email
                            </label>
                            <input type="email" 
                                   name="email" 
                                   id="email" 
                                   value="{{ old('email') }}"
                                   x-model="formData.email"
                                   required 
                                   autocomplete="email" 
                                   autofocus
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg input-focus transition-all duration-200 @error('email') border-red-500 @enderror"
                                   placeholder="Masukkan email Anda">
                            @error('email')
                            <p class="mt-1 text-sm text-red-600">
                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                {{ $message }}
                            </p>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div class="mb-6">
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-lock mr-2 text-gray-400"></i>
                                Password
                            </label>
                            <div class="relative">
                                <input :type="showPassword ? 'text' : 'password'" 
                                       name="password" 
                                       id="password" 
                                       x-model="formData.password"
                                       required 
                                       autocomplete="current-password"
                                       class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg input-focus transition-all duration-200 @error('password') border-red-500 @enderror"
                                       placeholder="Masukkan password Anda">
                                <button type="button" 
                                        @click="showPassword = !showPassword"
                                        class="absolute inset-y-0 right-0 flex items-center pr-4 text-gray-400 hover:text-gray-600">
                                    <i class="fas" :class="showPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
                                </button>
                            </div>
                            @error('password')
                            <p class="mt-1 text-sm text-red-600">
                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                {{ $message }}
                            </p>
                            @enderror
                        </div>

                        <!-- Remember Me & Forgot Password -->
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center">
                                <input type="checkbox" 
                                       name="remember" 
                                       id="remember" 
                                       x-model="formData.remember"
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="remember" class="ml-2 block text-sm text-gray-700">
                                    Ingat saya
                                </label>
                            </div>

                            @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" 
                               class="text-sm text-blue-600 hover:text-blue-800 transition-colors duration-200">
                                Lupa password?
                            </a>
                            @endif
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" 
                                :disabled="loading"
                                class="w-full btn-login text-white py-3 px-4 rounded-lg font-medium focus:outline-none focus:ring-4 focus:ring-blue-300 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span x-show="!loading" class="flex items-center justify-center">
                                <i class="fas fa-sign-in-alt mr-2"></i>
                                Masuk
                            </span>
                            <span x-show="loading" class="flex items-center justify-center">
                                <div class="w-5 h-5 loading-spinner mr-2"></div>
                                Memproses...
                            </span>
                        </button>
                    </form>

                    <!-- Divider -->
                    <div class="mt-8 mb-6">
                        <div class="relative">
                            <div class="absolute inset-0 flex items-center">
                                <div class="w-full border-t border-gray-300"></div>
                            </div>
                            <div class="relative flex justify-center text-sm">
                                <span class="px-2 bg-white text-gray-500">atau</span>
                            </div>
                        </div>
                    </div>

                    <!-- Register Link -->
                    @if (Route::has('register'))
                    <div class="text-center">
                        <p class="text-sm text-gray-600">
                            Belum punya akun? 
                            <a href="{{ route('register') }}" 
                               class="font-medium text-blue-600 hover:text-blue-800 transition-colors duration-200">
                                Daftar sekarang
                            </a>
                        </p>
                    </div>
                    @endif
                </div>

                <!-- Footer Info -->
                <div class="mt-8 text-center">
                    <p class="text-xs text-gray-500">
                        © {{ date('Y') }} {{ config('app.name') }}. 
                        <span class="block mt-1">
                            Dikembangkan untuk kemudahan manajemen HR Anda.
                        </span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-submit form on Enter
        document.addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && e.target.tagName.toLowerCase() !== 'button') {
                e.target.closest('form')?.submit();
            }
        });
        
        // Focus management
        document.addEventListener('DOMContentLoaded', function() {
            const emailInput = document.getElementById('email');
            if (emailInput && !emailInput.value) {
                emailInput.focus();
            }
        });
        
        // CSRF token refresh for preventing 419 errors
        function refreshCSRFToken() {
            fetch('/login', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newToken = doc.querySelector('meta[name="csrf-token"]').getAttribute('content');
                
                // Update meta tag
                document.querySelector('meta[name="csrf-token"]').setAttribute('content', newToken);
                
                // Update form tokens
                document.querySelectorAll('input[name="_token"]').forEach(input => {
                    input.value = newToken;
                });
            })
            .catch(error => {
                console.log('CSRF token refresh failed:', error);
            });
        }
        
        // Refresh CSRF token every 5 minutes
        setInterval(refreshCSRFToken, 5 * 60 * 1000);
        
        // Refresh token when page becomes visible (browser tab focus)
        document.addEventListener('visibilitychange', function() {
            if (!document.hidden) {
                refreshCSRFToken();
            }
        });
    </script>
</body>
</html>