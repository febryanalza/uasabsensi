@extends('layouts.dashboard')

@section('title', 'Management Karyawan')
@section('page-title', 'Management Karyawan')

@section('breadcrumb')
<li class="inline-flex items-center">
    <a href="{{ route('dashboard') }}" class="text-gray-500 hover:text-gray-700">
        <i class="fas fa-home mr-2"></i>
        Dashboard
    </a>
</li>
<li class="inline-flex items-center">
    <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
    <span class="text-gray-700 font-medium">Management Karyawan</span>
</li>
@endsection

@section('header-actions')
<a href="{{ route('karyawan.create') }}" 
   class="btn-primary text-white px-4 py-2 rounded-lg font-medium hover:shadow-lg transition-all duration-200">
    <i class="fas fa-plus mr-2"></i>
    Tambah Karyawan
</a>
@endsection

@section('content')
<div class="space-y-6" x-data="karyawanData()">
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
                           @input.debounce.500ms="searchKaryawan()"
                           placeholder="Cari berdasarkan nama, NIP, email..."
                           class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg input-focus">
                </div>
            </div>
            
            <!-- Filters -->
            <div class="flex items-center space-x-4">
                <select x-model="filters.status" 
                        @change="loadKaryawan()"
                        class="border border-gray-300 rounded-lg px-3 py-2 input-focus">
                    <option value="">Semua Status</option>
                    <option value="AKTIF">Aktif</option>
                    <option value="CUTI">Cuti</option>
                    <option value="RESIGN">Resign</option>
                </select>
                
                <select x-model="filters.departemen" 
                        @change="loadKaryawan()"
                        class="border border-gray-300 rounded-lg px-3 py-2 input-focus">
                    <option value="">Semua Departemen</option>
                    <option value="IT">IT</option>
                    <option value="HR">HR</option>
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
                    <p class="text-sm font-medium text-gray-600">Total Karyawan</p>
                    <p class="text-2xl font-bold text-gray-900" x-text="statistics.total">-</p>
                </div>
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-users text-blue-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl p-6 card-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Karyawan Aktif</p>
                    <p class="text-2xl font-bold text-green-600" x-text="statistics.aktif">-</p>
                </div>
                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-user-check text-green-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl p-6 card-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Cuti</p>
                    <p class="text-2xl font-bold text-orange-600" x-text="statistics.cuti">-</p>
                </div>
                <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-user-times text-red-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl p-6 card-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Resign</p>
                    <p class="text-2xl font-bold text-red-600" x-text="statistics.resign">-</p>
                </div>
                <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-user-times text-red-600"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Karyawan Table -->
    <div class="bg-white rounded-xl card-shadow">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">Daftar Karyawan</h2>
                <div class="flex items-center space-x-2 text-sm text-gray-500">
                    <span>Menampilkan</span>
                    <select x-model="pagination.per_page" 
                            @change="loadKaryawan()"
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
                            NIP / Email
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Departemen / Jabatan
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Tanggal Masuk
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
                            <td colspan="6" class="px-6 py-8 text-center">
                                <div class="flex items-center justify-center">
                                    <div class="w-6 h-6 spinner mr-3"></div>
                                    <span class="text-gray-500">Memuat data...</span>
                                </div>
                            </td>
                        </tr>
                    </template>

                    <!-- Empty state -->
                    <template x-if="!loading && karyawanList.length === 0">
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-users text-4xl text-gray-300 mb-4"></i>
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">Belum ada data karyawan</h3>
                                    <p class="text-gray-500 mb-4">Mulai dengan menambahkan karyawan pertama Anda</p>
                                    <a href="{{ route('karyawan.create') }}" 
                                       class="btn-primary text-white px-4 py-2 rounded-lg">
                                        <i class="fas fa-plus mr-2"></i>
                                        Tambah Karyawan
                                    </a>
                                </div>
                            </td>
                        </tr>
                    </template>

                    <!-- Data rows -->
                    <template x-for="karyawan in karyawanList" :key="karyawan.id">
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-gradient-to-r from-blue-600 to-purple-600 flex items-center justify-center">
                                            <span class="text-sm font-medium text-white" x-text="getInitials(karyawan.nama)"></span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900" x-text="karyawan.nama"></div>
                                        <div class="text-sm text-gray-500" x-text="karyawan.telepon || '-'"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900" x-text="karyawan.nip"></div>
                                <div class="text-sm text-gray-500" x-text="karyawan.email"></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900" x-text="karyawan.departemen || '-'"></div>
                                <div class="text-sm text-gray-500" x-text="karyawan.jabatan || '-'"></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="formatDate(karyawan.tanggal_masuk || karyawan.created_at)"></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                      :class="karyawan.status === 'AKTIF' ? 'bg-green-100 text-green-800' : (karyawan.status === 'CUTI' ? 'bg-orange-100 text-orange-800' : 'bg-red-100 text-red-800')"
                                      x-text="karyawan.status === 'AKTIF' ? 'Aktif' : (karyawan.status === 'CUTI' ? 'Cuti' : 'Resign')">
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                <button @click="viewKaryawan(karyawan.id)" 
                                        class="text-blue-600 hover:text-blue-900 transition-colors" 
                                        title="Lihat Detail">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button @click="editKaryawan(karyawan.id)" 
                                        class="text-yellow-600 hover:text-yellow-900 transition-colors" 
                                        title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button @click="confirmDelete(karyawan)" 
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
                    <p class="text-sm text-gray-500">Apakah Anda yakin ingin menghapus karyawan ini?</p>
                </div>
            </div>
            <div class="bg-gray-50 rounded-lg p-3 mb-4">
                <p class="text-sm"><strong>Nama:</strong> <span x-text="selectedKaryawan?.nama"></span></p>
                <p class="text-sm"><strong>NIP:</strong> <span x-text="selectedKaryawan?.nip"></span></p>
            </div>
            <div class="flex justify-end space-x-3">
                <button @click="showDeleteModal = false" 
                        class="px-4 py-2 text-gray-600 hover:text-gray-800 transition-colors">
                    Batal
                </button>
                <button @click="deleteKaryawan()" 
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
</div>
@endsection

@push('scripts')
<script>
function karyawanData() {
    return {
        karyawanList: [],
        loading: true,
        searchQuery: '',
        filters: {
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
            total: 0,
            aktif: 0,
            cuti: 0,
            resign: 0
        },
        showDeleteModal: false,
        selectedKaryawan: null,
        deleting: false,
        
        async init() {
            await this.loadStatistics();
            await this.loadKaryawan();
        },
        
        async loadStatistics() {
            try {
                const response = await fetch('/karyawan/api/statistics');
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
        
        async loadKaryawan() {
            try {
                this.loading = true;
                
                const params = new URLSearchParams({
                    page: this.pagination.current_page,
                    per_page: this.pagination.per_page
                });
                
                if (this.searchQuery) {
                    params.append('search', this.searchQuery);
                }
                
                if (this.filters.status) {
                    params.append('status', this.filters.status);
                }
                
                if (this.filters.departemen) {
                    params.append('departemen', this.filters.departemen);
                }
                
                const response = await fetch(`/karyawan/api/data?${params.toString()}`);
                
                if (response.ok) {
                    const data = await response.json();
                    if (data.success) {
                        this.karyawanList = data.data.data || [];
                        
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
                    showNotification('error', 'Gagal memuat data karyawan');
                }
            } catch (error) {
                console.error('Error loading karyawan:', error);
                showNotification('error', 'Gagal memuat data karyawan');
            } finally {
                this.loading = false;
            }
        },
        
        async searchKaryawan() {
            this.pagination.current_page = 1;
            await this.loadKaryawan();
        },
        
        resetFilters() {
            this.searchQuery = '';
            this.filters = { status: '', departemen: '' };
            this.pagination.current_page = 1;
            this.loadKaryawan();
        },
        
        viewKaryawan(id) {
            window.location.href = `/karyawan/${id}`;
        },
        
        editKaryawan(id) {
            window.location.href = `/karyawan/${id}/edit`;
        },
        
        confirmDelete(karyawan) {
            this.selectedKaryawan = karyawan;
            this.showDeleteModal = true;
        },
        
        async deleteKaryawan() {
            if (!this.selectedKaryawan) return;
            
            try {
                this.deleting = true;
                
                const response = await fetch(`/karyawan/api/${this.selectedKaryawan.id}/delete`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': window.CSRF_TOKEN
                    }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    if (data.success) {
                        showNotification('success', 'Karyawan berhasil dihapus');
                        this.showDeleteModal = false;
                        await this.loadKaryawan();
                        await this.loadStatistics();
                    } else {
                        showNotification('error', data.message || 'Gagal menghapus karyawan');
                    }
                } else {
                    showNotification('error', 'Gagal menghapus karyawan');
                }
            } catch (error) {
                console.error('Error deleting karyawan:', error);
                showNotification('error', 'Gagal menghapus karyawan');
            } finally {
                this.deleting = false;
            }
        },
        
        // Pagination methods
        previousPage() {
            if (this.pagination.current_page > 1) {
                this.pagination.current_page--;
                this.loadKaryawan();
            }
        },
        
        nextPage() {
            if (this.pagination.current_page < this.pagination.last_page) {
                this.pagination.current_page++;
                this.loadKaryawan();
            }
        },
        
        goToPage(page) {
            this.pagination.current_page = page;
            this.loadKaryawan();
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
            return name.split(' ').map(word => word[0]).join('').toUpperCase().substring(0, 2);
        },
        
        formatDate(dateString) {
            return new Date(dateString).toLocaleDateString('id-ID', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        }
    }
}
</script>
@endpush