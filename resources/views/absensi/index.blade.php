@extends('layouts.dashboard')

@section('title', 'Management Absensi')

@section('content')
<div x-data="absensiData()" x-init="init()" class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Management Absensi</h1>
            <p class="mt-2 text-sm text-gray-600">Kelola data kehadiran karyawan</p>
        </div>
        <div class="mt-4 sm:mt-0 flex space-x-3">
            <button @click="showBulkModal = true" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Import Absensi
            </button>
            <a href="{{ route('absensi.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Tambah Absensi
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200">
            <div class="flex items-center">
                <div class="p-2 bg-green-100 rounded-lg">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Hadir</p>
                    <p class="text-2xl font-bold text-gray-900" x-text="statistics.hadir || 0"></p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200">
            <div class="flex items-center">
                <div class="p-2 bg-yellow-100 rounded-lg">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Terlambat</p>
                    <p class="text-2xl font-bold text-gray-900" x-text="statistics.terlambat || 0"></p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200">
            <div class="flex items-center">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Izin/Sakit</p>
                    <p class="text-2xl font-bold text-gray-900" x-text="(statistics.izin || 0) + (statistics.sakit || 0)"></p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200">
            <div class="flex items-center">
                <div class="p-2 bg-red-100 rounded-lg">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Alpha</p>
                    <p class="text-2xl font-bold text-gray-900" x-text="statistics.alpha || 0"></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            <!-- Search -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Cari Karyawan</label>
                <input 
                    type="text" 
                    x-model="searchQuery"
                    @input.debounce.300ms="loadAbsensi()"
                    placeholder="Nama atau NIP..."
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
            </div>

            <!-- Status Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select 
                    x-model="filters.status"
                    @change="loadAbsensi()"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
                    <option value="">Semua Status</option>
                    <option value="HADIR">Hadir</option>
                    <option value="IZIN">Izin</option>
                    <option value="SAKIT">Sakit</option>
                    <option value="ALPHA">Alpha</option>
                    <option value="CUTI">Cuti</option>
                </select>
            </div>

            <!-- Date From -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Dari</label>
                <input 
                    type="date" 
                    x-model="filters.tanggal_dari"
                    @change="loadAbsensi()"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
            </div>

            <!-- Date To -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Sampai</label>
                <input 
                    type="date" 
                    x-model="filters.tanggal_sampai"
                    @change="loadAbsensi()"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
            </div>

            <!-- Month/Year Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Bulan/Tahun</label>
                <div class="flex space-x-2">
                    <select 
                        x-model="filters.bulan"
                        @change="loadAbsensi()"
                        class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="">Bulan</option>
                        <option value="1">Januari</option>
                        <option value="2">Februari</option>
                        <option value="3">Maret</option>
                        <option value="4">April</option>
                        <option value="5">Mei</option>
                        <option value="6">Juni</option>
                        <option value="7">Juli</option>
                        <option value="8">Agustus</option>
                        <option value="9">September</option>
                        <option value="10">Oktober</option>
                        <option value="11">November</option>
                        <option value="12">Desember</option>
                    </select>
                    <input 
                        type="number" 
                        x-model="filters.tahun"
                        @change="loadAbsensi()"
                        placeholder="2024"
                        min="2020"
                        max="2030"
                        class="w-20 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                </div>
            </div>
        </div>

        <div class="mt-4 flex space-x-2">
            <button 
                @click="resetFilters()"
                class="px-4 py-2 text-sm text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200"
            >
                Reset Filter
            </button>
            <button 
                @click="loadAbsensi()"
                class="px-4 py-2 text-sm text-white bg-blue-600 rounded-lg hover:bg-blue-700"
            >
                Terapkan Filter
            </button>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Karyawan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jam Masuk</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jam Keluar</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keterlambatan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <template x-for="absensi in absensiList" :key="absensi.id">
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-gradient-to-r from-blue-600 to-purple-600 flex items-center justify-center">
                                            <span class="text-sm font-medium text-white" x-text="getInitials(absensi.karyawan?.nama || '')"></span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900" x-text="absensi.karyawan?.nama || 'N/A'"></div>
                                        <div class="text-sm text-gray-500" x-text="absensi.karyawan?.nip || absensi.karyawan?.nik || 'N/A'"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <span x-text="formatDate(absensi.tanggal)"></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <span x-text="formatTime(absensi.jam_masuk)"></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <span x-text="formatTime(absensi.jam_keluar)"></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                      :class="getStatusClass(absensi.status)"
                                      x-text="absensi.status">
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <span x-show="absensi.menit_terlambat > 0" 
                                      x-text="absensi.menit_terlambat + ' menit'"
                                      class="text-red-600 font-medium">
                                </span>
                                <span x-show="!absensi.menit_terlambat || absensi.menit_terlambat === 0" class="text-green-600">
                                    Tepat waktu
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center space-x-2">
                                    <button @click="viewAbsensi(absensi)" 
                                            class="text-blue-600 hover:text-blue-900"
                                            title="Lihat Detail">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </button>
                                    <a :href="'/absensi/' + absensi.id + '/edit'" 
                                       class="text-indigo-600 hover:text-indigo-900"
                                       title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </a>
                                    <button @click="deleteConfirm(absensi)" 
                                            class="text-red-600 hover:text-red-900"
                                            title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
            
            <!-- Loading State -->
            <div x-show="loading" class="text-center py-8">
                <div class="inline-flex items-center px-4 py-2 text-sm text-gray-600">
                    <div class="w-4 h-4 spinner mr-2"></div>
                    Memuat data...
                </div>
            </div>
            
            <!-- Empty State -->
            <div x-show="!loading && absensiList.length === 0" class="text-center py-8">
                <div class="text-gray-500">
                    <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    <p class="text-lg font-medium">Tidak ada data absensi</p>
                    <p class="text-sm">Belum ada data absensi yang sesuai dengan filter yang dipilih</p>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div x-show="pagination.last_page > 1" class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
            <div class="flex-1 flex justify-between sm:hidden">
                <button @click="changePage(pagination.current_page - 1)"
                        :disabled="pagination.current_page <= 1"
                        class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50">
                    Previous
                </button>
                <button @click="changePage(pagination.current_page + 1)"
                        :disabled="pagination.current_page >= pagination.last_page"
                        class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50">
                    Next
                </button>
            </div>
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-gray-700">
                        Menampilkan
                        <span class="font-medium" x-text="pagination.from || 0"></span>
                        sampai
                        <span class="font-medium" x-text="pagination.to || 0"></span>
                        dari
                        <span class="font-medium" x-text="pagination.total || 0"></span>
                        hasil
                    </p>
                </div>
                <div>
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                        <button @click="changePage(pagination.current_page - 1)"
                                :disabled="pagination.current_page <= 1"
                                class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50">
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        </button>
                        
                        <template x-for="page in getVisiblePages()" :key="page">
                            <button @click="changePage(page)"
                                    :class="page === pagination.current_page ? 'bg-blue-50 border-blue-500 text-blue-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'"
                                    class="relative inline-flex items-center px-4 py-2 border text-sm font-medium"
                                    x-text="page">
                            </button>
                        </template>
                        
                        <button @click="changePage(pagination.current_page + 1)"
                                :disabled="pagination.current_page >= pagination.last_page"
                                class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50">
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div x-show="showDeleteModal" 
         x-transition:enter="ease-out duration-300" 
         x-transition:enter-start="opacity-0" 
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200" 
         x-transition:leave-start="opacity-100" 
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
         style="display: none;">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                    <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.996-.833-2.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mt-5">Hapus Data Absensi</h3>
                <div class="mt-2 px-7 py-3">
                    <p class="text-sm text-gray-500" x-show="selectedAbsensi">
                        Apakah Anda yakin ingin menghapus data absensi 
                        <strong x-text="selectedAbsensi?.karyawan?.nama"></strong> 
                        pada tanggal <strong x-text="formatDate(selectedAbsensi?.tanggal)"></strong>?
                    </p>
                </div>
                <div class="flex justify-center space-x-3 mt-4">
                    <button @click="showDeleteModal = false" 
                            class="px-4 py-2 bg-gray-300 text-gray-800 text-sm rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300">
                        Batal
                    </button>
                    <button @click="deleteAbsensi()" 
                            :disabled="deleting"
                            class="px-4 py-2 bg-red-600 text-white text-sm rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 disabled:opacity-50">
                        <span x-show="!deleting">Hapus</span>
                        <span x-show="deleting" class="flex items-center">
                            <div class="w-4 h-4 spinner mr-2"></div>
                            Menghapus...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function absensiData() {
    return {
        absensiList: [],
        statistics: {},
        searchQuery: '',
        loading: false,
        deleting: false,
        showDeleteModal: false,
        showBulkModal: false,
        selectedAbsensi: null,
        pagination: {
            current_page: 1,
            per_page: 15,
            total: 0,
            last_page: 1,
            from: 0,
            to: 0
        },
        filters: {
            status: '',
            tanggal_dari: '',
            tanggal_sampai: '',
            bulan: '',
            tahun: ''
        },
        
        init() {
            this.loadStatistics();
            this.loadAbsensi();
        },
        
        async loadStatistics() {
            try {
                const response = await fetch('/absensi/api/statistics');
                if (response.ok) {
                    const data = await response.json();
                    if (data.success) {
                        this.statistics = data.data;
                    }
                }
            } catch (error) {
                console.error('Error loading statistics:', error);
            }
        },
        
        async loadAbsensi() {
            try {
                this.loading = true;
                
                const params = new URLSearchParams({
                    page: this.pagination.current_page,
                    per_page: this.pagination.per_page
                });
                
                if (this.searchQuery) {
                    params.append('search', this.searchQuery);
                }
                
                Object.keys(this.filters).forEach(key => {
                    if (this.filters[key]) {
                        params.append(key, this.filters[key]);
                    }
                });
                
                const response = await fetch(`/absensi/api/data?${params.toString()}`);
                
                if (response.ok) {
                    const data = await response.json();
                    if (data.success) {
                        this.absensiList = data.data.data || [];
                        
                        // Update pagination info
                        if (data.data.current_page) {
                            this.pagination = {
                                current_page: data.data.current_page,
                                per_page: data.data.per_page,
                                total: data.data.total,
                                last_page: data.data.last_page,
                                from: data.data.from,
                                to: data.data.to
                            };
                        }
                    }
                } else {
                    showNotification('error', 'Gagal memuat data absensi');
                }
            } catch (error) {
                console.error('Error loading absensi:', error);
                showNotification('error', 'Gagal memuat data absensi');
            } finally {
                this.loading = false;
            }
        },
        
        changePage(page) {
            if (page >= 1 && page <= this.pagination.last_page) {
                this.pagination.current_page = page;
                this.loadAbsensi();
            }
        },
        
        getVisiblePages() {
            const current = this.pagination.current_page;
            const last = this.pagination.last_page;
            const pages = [];
            
            let start = Math.max(1, current - 2);
            let end = Math.min(last, current + 2);
            
            for (let i = start; i <= end; i++) {
                pages.push(i);
            }
            
            return pages;
        },
        
        resetFilters() {
            this.filters = {
                status: '',
                tanggal_dari: '',
                tanggal_sampai: '',
                bulan: '',
                tahun: ''
            };
            this.searchQuery = '';
            this.pagination.current_page = 1;
            this.loadAbsensi();
        },
        
        deleteConfirm(absensi) {
            this.selectedAbsensi = absensi;
            this.showDeleteModal = true;
        },
        
        async deleteAbsensi() {
            if (!this.selectedAbsensi) return;
            
            try {
                this.deleting = true;
                
                const response = await fetch(`/absensi/api/${this.selectedAbsensi.id}/delete`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': window.CSRF_TOKEN
                    }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    if (data.success) {
                        showNotification('success', 'Data absensi berhasil dihapus');
                        this.showDeleteModal = false;
                        await this.loadAbsensi();
                        await this.loadStatistics();
                    } else {
                        showNotification('error', data.message || 'Gagal menghapus data absensi');
                    }
                } else {
                    showNotification('error', 'Gagal menghapus data absensi');
                }
            } catch (error) {
                console.error('Error deleting absensi:', error);
                showNotification('error', 'Gagal menghapus data absensi');
            } finally {
                this.deleting = false;
            }
        },
        
        viewAbsensi(absensi) {
            // Implement view detail modal or redirect
            window.location.href = `/absensi/${absensi.id}`;
        },
        
        getStatusClass(status) {
            const classes = {
                'HADIR': 'bg-green-100 text-green-800',
                'IZIN': 'bg-blue-100 text-blue-800',
                'SAKIT': 'bg-yellow-100 text-yellow-800',
                'ALPHA': 'bg-red-100 text-red-800',
                'CUTI': 'bg-purple-100 text-purple-800'
            };
            return classes[status] || 'bg-gray-100 text-gray-800';
        },
        
        formatDate(dateString) {
            if (!dateString) return '-';
            return new Date(dateString).toLocaleDateString('id-ID', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit'
            });
        },
        
        formatTime(timeString) {
            if (!timeString) return '-';
            return new Date(timeString).toLocaleTimeString('id-ID', {
                hour: '2-digit',
                minute: '2-digit'
            });
        },
        
        getInitials(name) {
            if (!name) return 'N/A';
            return name.split(' ').map(word => word.charAt(0)).join('').toUpperCase().substring(0, 2);
        }
    }
}
</script>
@endpush