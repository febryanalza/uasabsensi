@extends('layouts.dashboard')

@section('title', 'Management Gaji')
@section('page-title', 'Management Gaji')

@section('breadcrumb')
<li class="inline-flex items-center">
    <a href="{{ route('dashboard') }}" class="text-gray-500 hover:text-gray-700">
        <i class="fas fa-home mr-2"></i>
        Dashboard
    </a>
</li>
<li class="inline-flex items-center">
    <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
    <span class="text-gray-700 font-medium">Management Gaji</span>
</li>
@endsection

@section('header-actions')
<div class="flex space-x-3">
    <button @click="showBulkCalculateModal = true" 
            class="btn-secondary text-gray-700 px-4 py-2 rounded-lg font-medium hover:shadow-lg transition-all duration-200">
        <i class="fas fa-calculator mr-2"></i>
        Hitung Gaji Bulk
    </button>
    <a href="{{ route('gaji.create') }}" 
       class="btn-primary text-white px-4 py-2 rounded-lg font-medium hover:shadow-lg transition-all duration-200">
        <i class="fas fa-plus mr-2"></i>
        Hitung Gaji
    </a>
</div>
@endsection

@section('content')
<div class="space-y-6" x-data="gajiData()">
    <!-- Filter and Search -->
    <div class="bg-white rounded-xl p-6 card-shadow">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
            <!-- Search -->
            <div class="flex-1 lg:max-w-lg">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input type="text" 
                           x-model="searchQuery"
                           @input.debounce.500ms="searchGaji()"
                           placeholder="Cari berdasarkan nama karyawan atau NIP..."
                           class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg input-focus">
                </div>
            </div>
            
            <!-- Filters -->
            <div class="flex items-center space-x-4">
                <select x-model="filters.bulan" 
                        @change="loadGaji()"
                        class="border border-gray-300 rounded-lg px-3 py-2 input-focus">
                    <option value="">Semua Bulan</option>
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
                
                <select x-model="filters.tahun" 
                        @change="loadGaji()"
                        class="border border-gray-300 rounded-lg px-3 py-2 input-focus">
                    <option value="">Semua Tahun</option>
                    <option value="2024">2024</option>
                    <option value="2025">2025</option>
                    <option value="2026">2026</option>
                </select>

                <select x-model="filters.status" 
                        @change="loadGaji()"
                        class="border border-gray-300 rounded-lg px-3 py-2 input-focus">
                    <option value="">Semua Status</option>
                    <option value="DRAFT">Draft</option>
                    <option value="FINAL">Final</option>
                    <option value="DIBAYAR">Dibayar</option>
                </select>
                
                <select x-model="filters.departemen" 
                        @change="loadGaji()"
                        class="border border-gray-300 rounded-lg px-3 py-2 input-focus">
                    <option value="">Semua Departemen</option>
                    <option value="Information Technology">IT</option>
                    <option value="Human Resources">HR</option>
                    <option value="Finance">Finance</option>
                    <option value="Marketing">Marketing</option>
                    <option value="Operations">Operations</option>
                </select>
                
                <button @click="resetFilters()" 
                        class="px-4 py-2 text-gray-600 hover:text-gray-800 transition-colors">
                    <i class="fas fa-times mr-1"></i>
                    Reset
                </button>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-xl p-6 card-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Gaji</p>
                    <p class="text-2xl font-bold text-gray-900" x-text="statistics.total_gaji || 0">-</p>
                </div>
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-money-bill-wave text-blue-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl p-6 card-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Pendapatan</p>
                    <p class="text-lg font-bold text-green-600" x-text="statistics.total_pendapatan_formatted || 'Rp 0'">-</p>
                </div>
                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-arrow-up text-green-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl p-6 card-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Potongan</p>
                    <p class="text-lg font-bold text-red-600" x-text="statistics.total_potongan_formatted || 'Rp 0'">-</p>
                </div>
                <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-arrow-down text-red-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl p-6 card-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Gaji Bersih</p>
                    <p class="text-lg font-bold text-purple-600" x-text="statistics.total_gaji_bersih_formatted || 'Rp 0'">-</p>
                </div>
                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-wallet text-purple-600"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Gaji Table -->
    <div class="bg-white rounded-xl card-shadow">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">Daftar Gaji Karyawan</h2>
                <div class="flex items-center space-x-2 text-sm text-gray-500">
                    <span>Menampilkan</span>
                    <select x-model="pagination.per_page" 
                            @change="loadGaji()"
                            class="border border-gray-300 rounded px-2 py-1">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                    <span>dari <span x-text="pagination.total">0</span> data</span>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Karyawan
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Periode
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Gaji Pokok
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Total Pendapatan
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Total Potongan
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Gaji Bersih
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <!-- Loading state -->
                    <template x-if="loading">
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center">
                                <div class="flex items-center justify-center">
                                    <div class="w-6 h-6 spinner mr-3"></div>
                                    <span class="text-gray-500">Memuat data...</span>
                                </div>
                            </td>
                        </tr>
                    </template>

                    <!-- Empty state -->
                    <template x-if="!loading && gajiList.length === 0">
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-money-bill-wave text-4xl text-gray-300 mb-4"></i>
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">Belum ada data gaji</h3>
                                    <p class="text-gray-500 mb-4">Mulai dengan menghitung gaji karyawan</p>
                                    <a href="{{ route('gaji.create') }}" 
                                       class="btn-primary text-white px-4 py-2 rounded-lg">
                                        <i class="fas fa-calculator mr-2"></i>
                                        Hitung Gaji
                                    </a>
                                </div>
                            </td>
                        </tr>
                    </template>

                    <!-- Data rows -->
                    <template x-for="gaji in gajiList" :key="gaji.id">
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-gradient-to-r from-blue-600 to-purple-600 flex items-center justify-center">
                                            <span class="text-sm font-medium text-white" x-text="getInitials(gaji.karyawan?.nama || 'N/A')"></span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900" x-text="gaji.karyawan?.nama || 'N/A'"></div>
                                        <div class="text-sm text-gray-500">
                                            <span x-text="gaji.karyawan?.nip || 'N/A'"></span> • 
                                            <span x-text="gaji.karyawan?.departemen || 'N/A'"></span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div x-text="formatPeriode(gaji.bulan, gaji.tahun)"></div>
                                <div class="text-xs text-gray-500" x-text="formatDate(gaji.created_at)"></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="formatCurrency(gaji.gaji_pokok)"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600" x-text="formatCurrency(gaji.total_pendapatan)"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-red-600" x-text="formatCurrency(gaji.total_potongan)"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-purple-600" x-text="formatCurrency(gaji.gaji_bersih)"></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                      :class="getStatusClass(gaji.status)"
                                      x-text="gaji.status">
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                <button @click="viewGaji(gaji.id)" 
                                        class="text-blue-600 hover:text-blue-900 transition-colors" 
                                        title="Lihat Detail">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button @click="editGaji(gaji.id)" 
                                        class="text-yellow-600 hover:text-yellow-900 transition-colors" 
                                        title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button @click="confirmDelete(gaji)" 
                                        x-show="gaji.status === 'DRAFT'"
                                        class="text-red-600 hover:text-red-900 transition-colors" 
                                        title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div x-show="pagination.total > pagination.per_page" 
             class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
            <div class="flex-1 flex justify-between sm:hidden">
                <button @click="previousPage()" 
                        :disabled="pagination.current_page <= 1"
                        class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                    Previous
                </button>
                <button @click="nextPage()" 
                        :disabled="pagination.current_page >= pagination.last_page"
                        class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                    Next
                </button>
            </div>
            
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-gray-700">
                        Showing
                        <span class="font-medium" x-text="pagination.from"></span>
                        to
                        <span class="font-medium" x-text="pagination.to"></span>
                        of
                        <span class="font-medium" x-text="pagination.total"></span>
                        results
                    </p>
                </div>
                <div>
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                        <button @click="previousPage()" 
                                :disabled="pagination.current_page <= 1"
                                class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        
                        <template x-for="page in visiblePages" :key="page">
                            <button @click="goToPage(page)" 
                                    :class="page === pagination.current_page ? 'bg-blue-50 border-blue-500 text-blue-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'"
                                    class="relative inline-flex items-center px-4 py-2 border text-sm font-medium"
                                    x-text="page">
                            </button>
                        </template>
                        
                        <button @click="nextPage()" 
                                :disabled="pagination.current_page >= pagination.last_page"
                                class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div x-show="showDeleteModal" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 bg-gray-900 bg-opacity-75 flex items-center justify-center"
         style="display: none;">
        <div class="bg-white rounded-lg p-6 max-w-md mx-4">
            <div class="flex items-center mb-4">
                <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center mr-4">
                    <i class="fas fa-exclamation-triangle text-red-600"></i>
                </div>
                <div>
                    <h3 class="text-lg font-medium text-gray-900">Konfirmasi Hapus</h3>
                    <p class="text-sm text-gray-500">Apakah Anda yakin ingin menghapus data gaji ini?</p>
                </div>
            </div>
            <div class="bg-gray-50 rounded-lg p-3 mb-4">
                <p class="text-sm"><strong>Karyawan:</strong> <span x-text="selectedGaji?.karyawan?.nama"></span></p>
                <p class="text-sm"><strong>Periode:</strong> <span x-text="formatPeriode(selectedGaji?.bulan, selectedGaji?.tahun)"></span></p>
            </div>
            <div class="flex justify-end space-x-3">
                <button @click="showDeleteModal = false" 
                        class="px-4 py-2 text-gray-600 hover:text-gray-800 transition-colors">
                    Batal
                </button>
                <button @click="deleteGaji()" 
                        :disabled="deleting"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors disabled:opacity-50">
                    <span x-show="!deleting">Hapus</span>
                    <span x-show="deleting" class="flex items-center">
                        <div class="w-4 h-4 spinner mr-2"></div>
                        Menghapus...
                    </span>
                </button>
            </div>
        </div>
    </div>

    <!-- Bulk Calculate Modal -->
    <div x-show="showBulkCalculateModal" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 bg-gray-900 bg-opacity-75 flex items-center justify-center"
         style="display: none;">
        <div class="bg-white rounded-lg p-6 max-w-lg mx-4">
            <div class="flex items-center mb-4">
                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mr-4">
                    <i class="fas fa-calculator text-blue-600"></i>
                </div>
                <div>
                    <h3 class="text-lg font-medium text-gray-900">Hitung Gaji Bulk</h3>
                    <p class="text-sm text-gray-500">Pilih periode dan karyawan untuk perhitungan gaji</p>
                </div>
            </div>
            
            <form @submit.prevent="bulkCalculateGaji()">
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Bulan</label>
                        <select x-model="bulkData.bulan" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2">
                            <option value="">Pilih Bulan</option>
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
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tahun</label>
                        <select x-model="bulkData.tahun" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2">
                            <option value="">Pilih Tahun</option>
                            <option value="2024">2024</option>
                            <option value="2025">2025</option>
                            <option value="2026">2026</option>
                        </select>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Semua Karyawan Aktif</label>
                    <label class="inline-flex items-center">
                        <input type="checkbox" x-model="bulkData.selectAll" 
                               class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        <span class="ml-2 text-sm text-gray-600">Pilih semua karyawan aktif untuk perhitungan</span>
                    </label>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" @click="showBulkCalculateModal = false" 
                            class="px-4 py-2 text-gray-600 hover:text-gray-800 transition-colors">
                        Batal
                    </button>
                    <button type="submit" 
                            :disabled="bulkCalculating"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50">
                        <span x-show="!bulkCalculating">Hitung Gaji</span>
                        <span x-show="bulkCalculating" class="flex items-center">
                            <div class="w-4 h-4 spinner mr-2"></div>
                            Menghitung...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function gajiData() {
    return {
        gajiList: [],
        loading: true,
        searchQuery: '',
        filters: {
            bulan: '',
            tahun: '',
            status: '',
            departemen: ''
        },
        pagination: {
            current_page: 1,
            per_page: 10,
            total: 0,
            last_page: 1,
            from: 0,
            to: 0
        },
        statistics: {
            total_gaji: 0,
            gaji_draft: 0,
            gaji_final: 0,
            gaji_dibayar: 0,
            total_pendapatan_formatted: 'Rp 0',
            total_potongan_formatted: 'Rp 0',
            total_gaji_bersih_formatted: 'Rp 0'
        },
        showDeleteModal: false,
        selectedGaji: null,
        deleting: false,
        showBulkCalculateModal: false,
        bulkCalculating: false,
        bulkData: {
            bulan: '',
            tahun: '',
            selectAll: true
        },
        
        async init() {
            await this.loadStatistics();
            await this.loadGaji();
        },
        
        async loadStatistics() {
            try {
                const params = new URLSearchParams();
                
                if (this.filters.bulan) params.append('bulan', this.filters.bulan);
                if (this.filters.tahun) params.append('tahun', this.filters.tahun);
                if (this.filters.departemen) params.append('departemen', this.filters.departemen);
                
                const response = await fetch(`/gaji/api/statistics?${params.toString()}`);
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
        
        async loadGaji() {
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
                
                const response = await fetch(`/gaji/api/data?${params.toString()}`);
                
                if (response.ok) {
                    const data = await response.json();
                    if (data.success) {
                        this.gajiList = data.data.data || [];
                        
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
                    showNotification('error', 'Gagal memuat data gaji');
                }
            } catch (error) {
                console.error('Error loading gaji:', error);
                showNotification('error', 'Gagal memuat data gaji');
            } finally {
                this.loading = false;
            }
        },
        
        async searchGaji() {
            this.pagination.current_page = 1;
            await Promise.all([this.loadGaji(), this.loadStatistics()]);
        },
        
        resetFilters() {
            this.searchQuery = '';
            this.filters = { bulan: '', tahun: '', status: '', departemen: '' };
            this.pagination.current_page = 1;
            this.loadGaji();
            this.loadStatistics();
        },
        
        viewGaji(id) {
            window.location.href = `/gaji/${id}`;
        },
        
        editGaji(id) {
            window.location.href = `/gaji/${id}/edit`;
        },
        
        confirmDelete(gaji) {
            this.selectedGaji = gaji;
            this.showDeleteModal = true;
        },
        
        async deleteGaji() {
            if (!this.selectedGaji) return;
            
            try {
                this.deleting = true;
                
                const response = await fetch(`/gaji/api/${this.selectedGaji.id}/delete`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': window.CSRF_TOKEN
                    }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    if (data.success) {
                        showNotification('success', 'Data gaji berhasil dihapus');
                        this.showDeleteModal = false;
                        await this.loadGaji();
                        await this.loadStatistics();
                    } else {
                        showNotification('error', data.message || 'Gagal menghapus data gaji');
                    }
                } else {
                    showNotification('error', 'Gagal menghapus data gaji');
                }
            } catch (error) {
                console.error('Error deleting gaji:', error);
                showNotification('error', 'Gagal menghapus data gaji');
            } finally {
                this.deleting = false;
            }
        },
        
        async bulkCalculateGaji() {
            if (!this.bulkData.bulan || !this.bulkData.tahun) {
                showNotification('error', 'Bulan dan tahun harus dipilih');
                return;
            }
            
            try {
                this.bulkCalculating = true;
                
                // Get all active karyawan IDs
                const karyawanResponse = await fetch('/gaji/api/karyawan-list');
                if (!karyawanResponse.ok) {
                    throw new Error('Gagal mengambil data karyawan');
                }
                
                const karyawanData = await karyawanResponse.json();
                if (!karyawanData.success) {
                    throw new Error('Gagal mengambil data karyawan');
                }
                
                const karyawanIds = karyawanData.data.map(k => k.id);
                
                // Bulk calculate
                const response = await fetch('/gaji/api/bulk-calculate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': window.CSRF_TOKEN
                    },
                    body: JSON.stringify({
                        karyawan_ids: karyawanIds,
                        bulan: parseInt(this.bulkData.bulan),
                        tahun: parseInt(this.bulkData.tahun)
                    })
                });
                
                if (response.ok) {
                    const data = await response.json();
                    if (data.success) {
                        const successCount = data.results.success.length;
                        const failedCount = data.results.failed.length;
                        
                        showNotification('success', 
                            `Bulk calculation selesai: ${successCount} berhasil, ${failedCount} gagal`);
                        this.showBulkCalculateModal = false;
                        this.bulkData = { bulan: '', tahun: '', selectAll: true };
                        await this.loadGaji();
                        await this.loadStatistics();
                    } else {
                        showNotification('error', data.message || 'Gagal melakukan bulk calculation');
                    }
                } else {
                    showNotification('error', 'Gagal melakukan bulk calculation');
                }
            } catch (error) {
                console.error('Error bulk calculating:', error);
                showNotification('error', error.message || 'Gagal melakukan bulk calculation');
            } finally {
                this.bulkCalculating = false;
            }
        },
        
        // Pagination methods
        previousPage() {
            if (this.pagination.current_page > 1) {
                this.pagination.current_page--;
                this.loadGaji();
            }
        },
        
        nextPage() {
            if (this.pagination.current_page < this.pagination.last_page) {
                this.pagination.current_page++;
                this.loadGaji();
            }
        },
        
        goToPage(page) {
            this.pagination.current_page = page;
            this.loadGaji();
        },
        
        get visiblePages() {
            const current = this.pagination.current_page;
            const last = this.pagination.last_page;
            const delta = 2;
            const range = [];
            
            for (let i = Math.max(2, current - delta); i <= Math.min(last - 1, current + delta); i++) {
                range.push(i);
            }
            
            if (current - delta > 2) {
                range.unshift('...');
            }
            if (current + delta < last - 1) {
                range.push('...');
            }
            
            range.unshift(1);
            if (last !== 1) {
                range.push(last);
            }
            
            return range.filter((v, i, arr) => arr.indexOf(v) === i);
        },
        
        // Helper methods
        getInitials(name) {
            if (!name) return 'N/A';
            return name.split(' ').map(word => word[0]).join('').toUpperCase().substring(0, 2);
        },
        
        formatCurrency(amount) {
            if (!amount) return 'Rp 0';
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
        },
        
        formatDate(dateString) {
            if (!dateString) return '-';
            return new Date(dateString).toLocaleDateString('id-ID', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
        },
        
        formatPeriode(bulan, tahun) {
            if (!bulan || !tahun) return '-';
            const months = [
                '', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
            ];
            return `${months[bulan]} ${tahun}`;
        },
        
        getStatusClass(status) {
            const classes = {
                'DRAFT': 'bg-yellow-100 text-yellow-800',
                'FINAL': 'bg-blue-100 text-blue-800',
                'DIBAYAR': 'bg-green-100 text-green-800'
            };
            return classes[status] || 'bg-gray-100 text-gray-800';
        }
    }
}
</script>
@endpush