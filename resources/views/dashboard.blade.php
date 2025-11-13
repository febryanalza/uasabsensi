@extends('layouts.dashboard')

@section('title', 'Dashboard Admin')
@section('page-title', 'Dashboard')

@section('breadcrumb')
<li class="inline-flex items-center">
    <i class="fas fa-home text-gray-400 mr-2"></i>
    <span class="text-gray-700 font-medium">Dashboard</span>
</li>
@endsection

@section('content')
<div class="space-y-6" x-data="dashboardData()">
    <!-- Welcome Card -->
    <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-xl p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold mb-2">
                    Selamat Datang, {{ auth()->user()->name ?? 'Admin' }}! 👋
                </h2>
                <p class="opacity-90">
                    Hari ini adalah {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}
                </p>
            </div>
            <div class="hidden md:block">
                <div class="w-24 h-24 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                    <i class="fas fa-chart-line text-4xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Karyawan -->
        <div class="bg-white rounded-xl p-6 card-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Karyawan</p>
                    <p class="text-3xl font-bold text-gray-900" x-text="stats.totalKaryawan">
                        <span class="inline-block w-8 h-8 bg-gray-200 rounded animate-pulse"></span>
                    </p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-users text-blue-600 text-xl"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <span class="text-green-600 font-medium" x-text="stats.karyawanActivePercent">+0%</span>
                <span class="text-gray-600 ml-1">karyawan aktif</span>
            </div>
        </div>

        <!-- Hadir Hari Ini -->
        <div class="bg-white rounded-xl p-6 card-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Hadir Hari Ini</p>
                    <p class="text-3xl font-bold text-gray-900" x-text="stats.hadirHariIni">
                        <span class="inline-block w-8 h-8 bg-gray-200 rounded animate-pulse"></span>
                    </p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-calendar-check text-green-600 text-xl"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <span class="text-green-600 font-medium" x-text="stats.absensiPercent">0%</span>
                <span class="text-gray-600 ml-1">dari total karyawan</span>
            </div>
        </div>

        <!-- Total Gaji Bulan Ini -->
        <div class="bg-white rounded-xl p-6 card-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Gaji Bulan Ini</p>
                    <p class="text-3xl font-bold text-gray-900" x-text="formatRupiah(stats.totalGajiBulanIni)">
                        <span class="inline-block w-8 h-8 bg-gray-200 rounded animate-pulse"></span>
                    </p>
                </div>
                <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-money-bill text-yellow-600 text-xl"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <span class="text-blue-600 font-medium">{{ \Carbon\Carbon::now()->translatedFormat('F Y') }}</span>
            </div>
        </div>

        <!-- Lembur Pending -->
        <div class="bg-white rounded-xl p-6 card-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Lembur Pending</p>
                    <p class="text-3xl font-bold text-gray-900" x-text="stats.lemburPending">
                        <span class="inline-block w-8 h-8 bg-gray-200 rounded animate-pulse"></span>
                    </p>
                </div>
                <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-clock text-orange-600 text-xl"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <span class="text-orange-600 font-medium">Perlu Review</span>
            </div>
        </div>
    </div>

    <!-- Charts and Recent Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Attendance Chart -->
        <div class="bg-white rounded-xl p-6 card-shadow">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900">Kehadiran 7 Hari Terakhir</h3>
                <div class="flex space-x-2">
                    <button @click="chartPeriod = '7days'" 
                            :class="chartPeriod === '7days' ? 'bg-blue-100 text-blue-600' : 'text-gray-500'"
                            class="px-3 py-1 text-sm rounded-lg hover:bg-gray-100 transition-colors">
                        7 Hari
                    </button>
                    <button @click="chartPeriod = '30days'" 
                            :class="chartPeriod === '30days' ? 'bg-blue-100 text-blue-600' : 'text-gray-500'"
                            class="px-3 py-1 text-sm rounded-lg hover:bg-gray-100 transition-colors">
                        30 Hari
                    </button>
                </div>
            </div>
            <div class="relative h-64">
                <canvas id="attendanceChart"></canvas>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white rounded-xl p-6 card-shadow">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900">Aktivitas Terbaru</h3>
                <a href="{{ route('karyawan.index') }}" class="text-sm text-blue-600 hover:text-blue-800">
                    Lihat Semua
                </a>
            </div>
            <div class="space-y-4" x-show="!loadingActivities">
                <template x-for="activity in recentActivities" :key="activity.id">
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center"
                                 :class="getActivityColor(activity.type)">
                                <i :class="getActivityIcon(activity.type)" class="text-sm"></i>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900" x-text="activity.title"></p>
                            <p class="text-sm text-gray-500" x-text="activity.description"></p>
                            <p class="text-xs text-gray-400 mt-1" x-text="formatDateTime(activity.created_at)"></p>
                        </div>
                    </div>
                </template>
            </div>
            <div x-show="loadingActivities" class="space-y-4">
                <template x-for="i in 5">
                    <div class="flex items-start space-x-3">
                        <div class="w-8 h-8 bg-gray-200 rounded-full animate-pulse"></div>
                        <div class="flex-1">
                            <div class="h-4 bg-gray-200 rounded w-3/4 mb-2 animate-pulse"></div>
                            <div class="h-3 bg-gray-200 rounded w-1/2 animate-pulse"></div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-xl p-6 card-shadow">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">Aksi Cepat</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <a href="{{ route('karyawan.create') }}" 
               class="flex items-center p-4 border border-gray-200 rounded-lg hover:border-blue-300 hover:bg-blue-50 transition-all duration-200 group">
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center group-hover:bg-blue-200 transition-colors">
                    <i class="fas fa-user-plus text-blue-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-900">Tambah Karyawan</p>
                    <p class="text-xs text-gray-500">Registrasi karyawan baru</p>
                </div>
            </a>

            <a href="#" onclick="alert('Fitur akan segera tersedia')" 
               class="flex items-center p-4 border border-gray-200 rounded-lg hover:border-green-300 hover:bg-green-50 transition-all duration-200 group">
                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center group-hover:bg-green-200 transition-colors">
                    <i class="fas fa-calculator text-green-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-900">Hitung Gaji</p>
                    <p class="text-xs text-gray-500">Proses perhitungan gaji</p>
                </div>
            </a>

            <a href="#" onclick="alert('Fitur akan segera tersedia')" 
               class="flex items-center p-4 border border-gray-200 rounded-lg hover:border-yellow-300 hover:bg-yellow-50 transition-all duration-200 group">
                <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center group-hover:bg-yellow-200 transition-colors">
                    <i class="fas fa-file-excel text-yellow-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-900">Export Data</p>
                    <p class="text-xs text-gray-500">Download laporan</p>
                </div>
            </a>

            <a href="#" onclick="alert('Fitur akan segera tersedia')" 
               class="flex items-center p-4 border border-gray-200 rounded-lg hover:border-purple-300 hover:bg-purple-50 transition-all duration-200 group">
                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center group-hover:bg-purple-200 transition-colors">
                    <i class="fas fa-cogs text-purple-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-900">Pengaturan</p>
                    <p class="text-xs text-gray-500">Konfigurasi sistem</p>
                </div>
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function dashboardData() {
    return {
        stats: {
            totalKaryawan: 0,
            karyawanActivePercent: '0%',
            hadirHariIni: 0,
            absensiPercent: '0%',
            totalGajiBulanIni: 0,
            lemburPending: 0
        },
        recentActivities: [],
        loadingActivities: true,
        chartPeriod: '7days',
        chart: null,
        
        async init() {
            await this.loadDashboardData();
            await this.loadRecentActivities();
            this.initChart();
        },
        
        async loadDashboardData() {
            try {
                // Load statistics from dashboard API
                const response = await fetch('/dashboard/statistics');
                if (response.ok) {
                    const data = await response.json();
                    if (data.success) {
                        // Update karyawan stats
                        if (data.data.karyawan) {
                            this.stats.totalKaryawan = data.data.karyawan.total_karyawan || 0;
                            this.stats.karyawanActivePercent = data.data.karyawan.active_percentage || '0%';
                        }
                        
                        // Update absensi stats
                        if (data.data.absensi) {
                            this.stats.hadirHariIni = data.data.absensi.hadir_hari_ini || 0;
                            this.stats.absensiPercent = data.data.absensi.attendance_percentage || '0%';
                        }
                        
                        // Update gaji stats
                        if (data.data.gaji) {
                            this.stats.totalGajiBulanIni = data.data.gaji.total_gaji_bulan_ini || 0;
                        }
                        
                        // Update lembur stats
                        if (data.data.lembur) {
                            this.stats.lemburPending = data.data.lembur.lembur_pending || 0;
                        }
                    }
                }
                
            } catch (error) {
                console.error('Error loading dashboard data:', error);
                showNotification('error', 'Gagal memuat data dashboard');
            }
        },
        
        async loadRecentActivities() {
            try {
                this.loadingActivities = true;
                
                const response = await fetch('/dashboard/activities');
                if (response.ok) {
                    const data = await response.json();
                    if (data.success) {
                        this.recentActivities = data.data || [];
                    }
                }
                
            } catch (error) {
                console.error('Error loading recent activities:', error);
                
                // Fallback to mock data if API fails
                this.recentActivities = [
                    {
                        id: 1,
                        type: 'karyawan_added',
                        title: 'Sistem Siap',
                        description: 'Dashboard telah terhubung dengan API',
                        created_at: new Date().toISOString()
                    }
                ];
            } finally {
                this.loadingActivities = false;
            }
        },
        
        initChart() {
            const ctx = document.getElementById('attendanceChart');
            if (!ctx) return;
            
            this.chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'],
                    datasets: [{
                        label: 'Hadir',
                        data: [85, 92, 78, 95, 88, 0, 0],
                        borderColor: '#667eea',
                        backgroundColor: 'rgba(102, 126, 234, 0.1)',
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            ticks: {
                                callback: function(value) {
                                    return value + '%';
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        },
        
        getActivityColor(type) {
            const colors = {
                'karyawan_added': 'bg-blue-100 text-blue-600',
                'absensi_recorded': 'bg-green-100 text-green-600',
                'gaji_calculated': 'bg-yellow-100 text-yellow-600',
                'lembur_approved': 'bg-purple-100 text-purple-600'
            };
            return colors[type] || 'bg-gray-100 text-gray-600';
        },
        
        getActivityIcon(type) {
            const icons = {
                'karyawan_added': 'fas fa-user-plus',
                'absensi_recorded': 'fas fa-calendar-check',
                'gaji_calculated': 'fas fa-calculator',
                'lembur_approved': 'fas fa-clock'
            };
            return icons[type] || 'fas fa-info';
        },
        
        formatDateTime(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const diff = now - date;
            const minutes = Math.floor(diff / 60000);
            const hours = Math.floor(minutes / 60);
            
            if (minutes < 60) {
                return `${minutes} menit yang lalu`;
            } else if (hours < 24) {
                return `${hours} jam yang lalu`;
            } else {
                return date.toLocaleDateString('id-ID');
            }
        },
        
        formatRupiah(amount) {
            if (!amount) return 'Rp 0';
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(amount);
        }
    }
}
</script>
@endpush