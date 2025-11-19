<!DOCTYPE html>
<html lang="id" class="h-full bg-gray-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard Admin') - {{ config('app.name') }}</title>

    <!-- Tailwind CSS untuk shared hosting compatibility -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Alpine.js untuk interaktivitas -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Font Awesome untuk icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Chart.js untuk statistik -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Custom styles -->
    <style>
        .sidebar-transition {
            transition: all 0.3s ease-in-out;
        }
        
        .table-hover tbody tr:hover {
            background-color: #f8fafc;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        
        .card-shadow {
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            transition: all 0.3s ease;
        }
        
        .card-shadow:hover {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        
        /* Loading spinner */
        .spinner {
            border: 2px solid #f3f4f6;
            border-top: 2px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Custom scrollbar */
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }
        
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 3px;
        }
        
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }
        
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
</head>

<body class="h-full" x-data="{ 
    sidebarOpen: false, 
    currentPage: '{{ request()->route()->getName() }}',
    loading: false,
    showNotification: false,
    notification: { type: 'success', message: '' }
}">
    <!-- Mobile sidebar backdrop -->
    <div x-show="sidebarOpen" 
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 bg-gray-900 bg-opacity-75 lg:hidden"
         @click="sidebarOpen = false"></div>

    <!-- Sidebar -->
    <div class="fixed inset-y-0 left-0 z-50 w-64 bg-white shadow-lg sidebar-transition lg:translate-x-0"
         :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
         x-transition:enter="transition ease-in-out duration-300"
         x-transition:enter-start="-translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transition ease-in-out duration-300"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="-translate-x-full">
        
        <!-- Logo -->
        <div class="flex items-center justify-center h-16 px-4 bg-gradient-to-r from-blue-600 to-purple-600">
            <div class="flex items-center space-x-3">
                <div class="flex items-center justify-center w-8 h-8 bg-white rounded-lg">
                    <i class="fas fa-building text-blue-600"></i>
                </div>
                <span class="text-xl font-bold text-white">HRSystem</span>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 px-4 py-6 space-y-2 custom-scrollbar overflow-y-auto">
            <!-- Dashboard -->
            <a href="{{ route('dashboard') }}" 
               class="flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors duration-200"
               :class="currentPage === 'dashboard' ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-gray-100'">
                <i class="fas fa-home w-5 h-5 mr-3"></i>
                Dashboard
            </a>

            <!-- Management Karyawan -->
            <div x-data="{ expanded: {{ request()->routeIs('karyawan*') ? 'true' : 'false' }} }">
                <button @click="expanded = !expanded"
                        class="flex items-center justify-between w-full px-4 py-3 text-sm font-medium text-gray-700 rounded-lg hover:bg-gray-100 transition-colors duration-200">
                    <div class="flex items-center">
                        <i class="fas fa-users w-5 h-5 mr-3"></i>
                        Management Karyawan
                    </div>
                    <i class="fas fa-chevron-down transform transition-transform duration-200"
                       :class="expanded ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="expanded" 
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 transform -translate-y-2"
                     x-transition:enter-end="opacity-100 transform translate-y-0"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 transform translate-y-0"
                     x-transition:leave-end="opacity-0 transform -translate-y-2"
                     class="ml-8 mt-2 space-y-1">
                    <a href="{{ route('karyawan.index') }}" 
                       class="flex items-center px-4 py-2 text-sm rounded-lg transition-colors duration-200"
                       :class="currentPage === 'karyawan.index' ? 'bg-blue-100 text-blue-700' : 'text-gray-600 hover:bg-gray-100'">
                        <i class="fas fa-list w-4 h-4 mr-3"></i>
                        Daftar Karyawan
                    </a>
                    <a href="{{ route('karyawan.create') }}" 
                       class="flex items-center px-4 py-2 text-sm rounded-lg transition-colors duration-200"
                       :class="currentPage === 'karyawan.create' ? 'bg-blue-100 text-blue-700' : 'text-gray-600 hover:bg-gray-100'">
                        <i class="fas fa-plus w-4 h-4 mr-3"></i>
                        Tambah Karyawan
                    </a>
                </div>
            </div>

            <!-- Management Absensi -->
            <div x-data="{ expanded: {{ request()->routeIs('absensi*') ? 'true' : 'false' }} }">
                <button @click="expanded = !expanded"
                        class="flex items-center justify-between w-full px-4 py-3 text-sm font-medium text-gray-700 rounded-lg hover:bg-gray-100 transition-colors duration-200">
                    <div class="flex items-center">
                        <i class="fas fa-clipboard-check w-5 h-5 mr-3"></i>
                        Management Absensi
                    </div>
                    <i class="fas fa-chevron-down transform transition-transform duration-200"
                       :class="expanded ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="expanded" 
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 transform -translate-y-2"
                     x-transition:enter-end="opacity-100 transform translate-y-0"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 transform translate-y-0"
                     x-transition:leave-end="opacity-0 transform -translate-y-2"
                     class="ml-8 mt-2 space-y-1">
                    <a href="{{ route('absensi.index') }}" 
                       class="flex items-center px-4 py-2 text-sm rounded-lg transition-colors duration-200"
                       :class="currentPage === 'absensi.index' ? 'bg-blue-100 text-blue-700' : 'text-gray-600 hover:bg-gray-100'">
                        <i class="fas fa-list w-4 h-4 mr-3"></i>
                        Data Absensi
                    </a>
                    <a href="{{ route('absensi.create') }}" 
                       class="flex items-center px-4 py-2 text-sm rounded-lg transition-colors duration-200"
                       :class="currentPage === 'absensi.create' ? 'bg-blue-100 text-blue-700' : 'text-gray-600 hover:bg-gray-100'">
                        <i class="fas fa-plus w-4 h-4 mr-3"></i>
                        Tambah Absensi
                    </a>
                </div>
            </div>

            <!-- Management Gaji -->
            <div x-data="{ expanded: {{ request()->routeIs('gaji*') ? 'true' : 'false' }} }">
                <button @click="expanded = !expanded"
                        class="flex items-center justify-between w-full px-4 py-3 text-sm font-medium text-gray-700 rounded-lg hover:bg-gray-100 transition-colors duration-200">
                    <div class="flex items-center">
                        <i class="fas fa-money-bill-wave w-5 h-5 mr-3"></i>
                        Management Gaji
                    </div>
                    <i class="fas fa-chevron-down transform transition-transform duration-200"
                       :class="expanded ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="expanded" 
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 transform -translate-y-2"
                     x-transition:enter-end="opacity-100 transform translate-y-0"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 transform translate-y-0"
                     x-transition:leave-end="opacity-0 transform -translate-y-2"
                     class="ml-8 mt-2 space-y-1">
                    <a href="{{ route('gaji.index') }}" 
                       class="flex items-center px-4 py-2 text-sm rounded-lg transition-colors duration-200"
                       :class="currentPage === 'gaji.index' ? 'bg-blue-100 text-blue-700' : 'text-gray-600 hover:bg-gray-100'">
                        <i class="fas fa-list w-4 h-4 mr-3"></i>
                        Daftar Gaji
                    </a>
                    <a href="{{ route('gaji.create') }}" 
                       class="flex items-center px-4 py-2 text-sm rounded-lg transition-colors duration-200"
                       :class="currentPage === 'gaji.create' ? 'bg-blue-100 text-blue-700' : 'text-gray-600 hover:bg-gray-100'">
                        <i class="fas fa-calculator w-4 h-4 mr-3"></i>
                        Hitung Gaji
                    </a>
                </div>
            </div>

            <!-- Aturan Perusahaan -->
            <div x-data="{ expanded: {{ request()->routeIs('aturan*') ? 'true' : 'false' }} }">
                <button @click="expanded = !expanded"
                        class="flex items-center justify-between w-full px-4 py-3 text-sm font-medium text-gray-700 rounded-lg hover:bg-gray-100 transition-colors duration-200">
                    <div class="flex items-center">
                        <i class="fas fa-gavel w-5 h-5 mr-3"></i>
                        Aturan Perusahaan
                    </div>
                    <i class="fas fa-chevron-down transform transition-transform duration-200"
                       :class="expanded ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="expanded" 
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 transform -translate-y-2"
                     x-transition:enter-end="opacity-100 transform translate-y-0"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 transform translate-y-0"
                     x-transition:leave-end="opacity-0 transform -translate-y-2"
                     class="ml-8 mt-2 space-y-1">
                    <a href="{{ route('aturan.index') }}" 
                       class="flex items-center px-4 py-2 text-sm rounded-lg transition-colors duration-200"
                       :class="currentPage === 'aturan.index' ? 'bg-blue-100 text-blue-700' : 'text-gray-600 hover:bg-gray-100'">
                        <i class="fas fa-list w-4 h-4 mr-3"></i>
                        Daftar Aturan
                    </a>
                    <a href="{{ route('aturan.create') }}" 
                       class="flex items-center px-4 py-2 text-sm rounded-lg transition-colors duration-200"
                       :class="currentPage === 'aturan.create' ? 'bg-blue-100 text-blue-700' : 'text-gray-600 hover:bg-gray-100'">
                        <i class="fas fa-plus w-4 h-4 mr-3"></i>
                        Tambah Aturan
                    </a>
                </div>
            </div>
        </nav>

        <!-- User info -->
        <div class="p-4 border-t border-gray-200">
            <div class="flex items-center space-x-3">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-gradient-to-r from-blue-600 to-purple-600 rounded-full flex items-center justify-center">
                        <i class="fas fa-user text-white text-sm"></i>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate">
                        {{ auth()->user()->name ?? 'Admin User' }}
                    </p>
                    <p class="text-xs text-gray-500 truncate">
                        {{ auth()->user()->email ?? 'admin@example.com' }}
                    </p>
                </div>
            </div>
            <div class="mt-3">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" 
                            class="w-full flex items-center px-3 py-2 text-sm text-red-600 hover:bg-red-50 rounded-lg transition-colors duration-200">
                        <i class="fas fa-sign-out-alt w-4 h-4 mr-3"></i>
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <div class="flex-1 lg:ml-64">
        <!-- Top navbar -->
        <header class="bg-white shadow-sm border-b border-gray-200">
            <div class="flex items-center justify-between px-4 py-4 sm:px-6">
                <!-- Mobile menu button -->
                <button @click="sidebarOpen = true" 
                        class="text-gray-500 hover:text-gray-700 focus:outline-none lg:hidden">
                    <i class="fas fa-bars w-6 h-6"></i>
                </button>

                <!-- Page title -->
                <div class="flex-1 lg:flex lg:items-center lg:justify-between">
                    <h1 class="text-2xl font-semibold text-gray-900">
                        @yield('page-title', 'Dashboard')
                    </h1>
                    
                    <!-- Header actions -->
                    <div class="flex items-center space-x-4">
                        @yield('header-actions')
                    </div>
                </div>
            </div>
        </header>

        <!-- Page content -->
        <main class="flex-1 p-6">
            <!-- Breadcrumb -->
            @hasSection('breadcrumb')
            <nav class="flex mb-6" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    @yield('breadcrumb')
                </ol>
            </nav>
            @endif

            <!-- Flash messages -->
            @if(session('success'))
            <div x-data="{ show: true }" 
                 x-show="show" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform scale-90"
                 x-transition:enter-end="opacity-100 transform scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 transform scale-100"
                 x-transition:leave-end="opacity-0 transform scale-90"
                 class="mb-6 p-4 rounded-lg bg-green-50 border border-green-200">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle text-green-400 mr-3"></i>
                        <span class="text-green-800">{{ session('success') }}</span>
                    </div>
                    <button @click="show = false" class="text-green-400 hover:text-green-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            @endif

            @if(session('error'))
            <div x-data="{ show: true }" 
                 x-show="show" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform scale-90"
                 x-transition:enter-end="opacity-100 transform scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 transform scale-100"
                 x-transition:leave-end="opacity-0 transform scale-90"
                 class="mb-6 p-4 rounded-lg bg-red-50 border border-red-200">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle text-red-400 mr-3"></i>
                        <span class="text-red-800">{{ session('error') }}</span>
                    </div>
                    <button @click="show = false" class="text-red-400 hover:text-red-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            @endif

            <!-- Loading overlay -->
            <div x-show="loading" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 z-50 bg-gray-900 bg-opacity-75 flex items-center justify-center"
                 style="display: none;">
                <div class="bg-white rounded-lg p-6 flex items-center space-x-3">
                    <div class="w-6 h-6 spinner"></div>
                    <span class="text-gray-700">Loading...</span>
                </div>
            </div>

            <!-- Main content area -->
            @yield('content')
        </main>
    </div>

    <!-- Global JavaScript -->
    <script>
        // Global functions for shared hosting compatibility
        window.API_BASE_URL = '{{ url("api") }}';
        
        // CSRF token for API calls
        window.CSRF_TOKEN = '{{ csrf_token() }}';
        
        // Helper function for API calls
        async function apiCall(endpoint, options = {}) {
            const defaultOptions = {
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.CSRF_TOKEN,
                    'Accept': 'application/json'
                }
            };
            
            const finalOptions = {
                ...defaultOptions,
                ...options,
                headers: {
                    ...defaultOptions.headers,
                    ...options.headers
                }
            };
            
            try {
                const response = await fetch(`${window.API_BASE_URL}${endpoint}`, finalOptions);
                const data = await response.json();
                
                if (!response.ok) {
                    throw new Error(data.message || 'Something went wrong');
                }
                
                return data;
            } catch (error) {
                console.error('API call failed:', error);
                throw error;
            }
        }
        
        // Format currency for Indonesian Rupiah
        function formatRupiah(amount) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(amount);
        }
        
        // Format date for Indonesian locale
        function formatDate(date) {
            return new Date(date).toLocaleDateString('id-ID', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        }
        
        // Show notification
        function showNotification(type, message) {
            // This will be handled by Alpine.js
            document.dispatchEvent(new CustomEvent('show-notification', {
                detail: { type, message }
            }));
        }
        
        // Auto-hide mobile sidebar when clicking on links
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarLinks = document.querySelectorAll('.sidebar a');
            sidebarLinks.forEach(link => {
                link.addEventListener('click', function() {
                    // Close mobile sidebar
                    if (window.innerWidth < 1024) {
                        Alpine.store('sidebarOpen', false);
                    }
                });
            });
        });
    </script>

    @stack('scripts')
</body>
</html>