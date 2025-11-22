@extends('layouts.dashboard')

@section('title', 'RFID Management')

@section('breadcrumb')
<li>
    <div class="flex items-center">
        <a href="{{ route('dashboard') }}" class="ml-1 text-gray-400 hover:text-gray-500 md:ml-2">
            <i class="fas fa-home"></i>
        </a>
    </div>
</li>
<li>
    <div class="flex items-center">
        <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
        <span class="ml-1 text-gray-500 md:ml-2">RFID Management</span>
    </div>
</li>
@endsection

@section('content')
<div class="space-y-6" x-data="rfidManagement()">
    <!-- Header Section -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">RFID Management</h2>
                <p class="mt-1 text-sm text-gray-600">Kelola kartu RFID dan penugasan karyawan</p>
            </div>
            <div class="mt-4 sm:mt-0 flex space-x-3">
                <button @click="refreshData()" 
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                    <i class="fas fa-sync-alt mr-2"></i>
                    Refresh
                </button>
                <button @click="exportData()" 
                        class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-lg text-sm font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
                    <i class="fas fa-download mr-2"></i>
                    Export
                </button>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-credit-card text-blue-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Kartu</p>
                    <p class="text-2xl font-bold text-gray-900" x-text="statistics.total_cards || 0"></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Tersedia</p>
                    <p class="text-2xl font-bold text-green-600" x-text="statistics.available_cards || 0"></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-user-check text-purple-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Tertugaskan</p>
                    <p class="text-2xl font-bold text-purple-600" x-text="statistics.assigned_cards || 0"></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-exclamation-triangle text-red-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Bermasalah</p>
                    <p class="text-2xl font-bold text-red-600" x-text="(statistics.damaged_cards || 0) + (statistics.lost_cards || 0)"></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Search -->
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Pencarian</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input type="text" 
                           x-model="filters.search"
                           @input.debounce.300ms="loadData()"
                           placeholder="Cari nomor kartu, karyawan..."
                           class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>

            <!-- Status Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select x-model="filters.status" 
                        @change="loadData()"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Semua Status</option>
                    <option value="AVAILABLE">Tersedia</option>
                    <option value="ASSIGNED">Tertugaskan</option>
                    <option value="DAMAGED">Rusak</option>
                    <option value="LOST">Hilang</option>
                    <option value="INACTIVE">Tidak Aktif</option>
                </select>
            </div>

            <!-- Assignment Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Penugasan</label>
                <select x-model="filters.assigned" 
                        @change="loadData()"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Semua</option>
                    <option value="true">Tertugaskan</option>
                    <option value="false">Belum Tertugaskan</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <h3 class="text-lg font-medium text-gray-900">Daftar Kartu RFID</h3>
                <div class="mt-2 sm:mt-0 flex items-center space-x-2">
                    <span class="text-sm text-gray-600" x-text="`Menampilkan ${pagination.from || 0} - ${pagination.to || 0} dari ${pagination.total || 0} data`"></span>
                </div>
            </div>
        </div>

        <!-- Loading State -->
        <div x-show="loading" class="flex items-center justify-center py-12">
            <div class="flex items-center space-x-3">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                <span class="text-gray-600">Memuat data...</span>
            </div>
        </div>

        <!-- Table -->
        <div x-show="!loading" class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <button @click="sort('card_number')" class="group inline-flex">
                                Nomor Kartu
                                <span class="ml-2 flex-none rounded text-gray-400 group-hover:text-gray-600">
                                    <i class="fas fa-sort"></i>
                                </span>
                            </button>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <button @click="sort('card_type')" class="group inline-flex">
                                Tipe Kartu
                                <span class="ml-2 flex-none rounded text-gray-400 group-hover:text-gray-600">
                                    <i class="fas fa-sort"></i>
                                </span>
                            </button>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <button @click="sort('status')" class="group inline-flex">
                                Status
                                <span class="ml-2 flex-none rounded text-gray-400 group-hover:text-gray-600">
                                    <i class="fas fa-sort"></i>
                                </span>
                            </button>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Karyawan
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <button @click="sort('assigned_at')" class="group inline-flex">
                                Tanggal Ditugaskan
                                <span class="ml-2 flex-none rounded text-gray-400 group-hover:text-gray-600">
                                    <i class="fas fa-sort"></i>
                                </span>
                            </button>
                        </th>
                        <th scope="col" class="relative px-6 py-3">
                            <span class="sr-only">Aksi</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <template x-for="card in cards" :key="card.id">
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                <span x-text="card.card_number"></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <span x-text="card.card_type"></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                      :class="{
                                          'bg-green-100 text-green-800': card.status === 'AVAILABLE',
                                          'bg-purple-100 text-purple-800': card.status === 'ASSIGNED',
                                          'bg-red-100 text-red-800': card.status === 'DAMAGED',
                                          'bg-orange-100 text-orange-800': card.status === 'LOST',
                                          'bg-gray-100 text-gray-800': card.status === 'INACTIVE'
                                      }"
                                      x-text="getStatusText(card.status)">
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <div x-show="card.karyawan">
                                    <div class="font-medium text-gray-900" x-text="card.karyawan?.nama"></div>
                                    <div class="text-gray-500" x-text="card.karyawan?.nip"></div>
                                    <div class="text-xs text-gray-400" x-text="card.karyawan?.departemen + ' - ' + card.karyawan?.jabatan"></div>
                                </div>
                                <span x-show="!card.karyawan" class="text-gray-400 italic">Belum ditugaskan</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <span x-show="card.assigned_at" x-text="formatDate(card.assigned_at)"></span>
                                <span x-show="!card.assigned_at" class="text-gray-400 italic">-</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end space-x-2">
                                    <button @click="editCard(card)" 
                                            class="text-blue-600 hover:text-blue-900 transition-colors">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button @click="deleteCard(card)" 
                                            class="text-red-600 hover:text-red-900 transition-colors">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>

            <!-- Empty State -->
            <div x-show="cards.length === 0 && !loading" class="text-center py-12">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 rounded-full mb-4">
                    <i class="fas fa-credit-card text-gray-400 text-xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak ada data RFID</h3>
                <p class="text-gray-500">Belum ada kartu RFID yang terdaftar dalam sistem.</p>
            </div>
        </div>

        <!-- Pagination -->
        <div x-show="pagination.last_page > 1" class="px-6 py-4 border-t border-gray-200 bg-gray-50">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <span class="text-sm text-gray-700">
                        Menampilkan <span class="font-medium" x-text="pagination.from"></span> - 
                        <span class="font-medium" x-text="pagination.to"></span> dari 
                        <span class="font-medium" x-text="pagination.total"></span> hasil
                    </span>
                </div>
                <nav class="flex items-center space-x-2">
                    <button @click="goToPage(pagination.current_page - 1)"
                            :disabled="pagination.current_page <= 1"
                            :class="pagination.current_page <= 1 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-200'"
                            class="px-3 py-1 rounded-md border border-gray-300 bg-white text-sm font-medium text-gray-700 transition-colors">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    
                    <template x-for="page in getVisiblePages()" :key="page">
                        <button @click="goToPage(page)"
                                :class="page === pagination.current_page ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-200'"
                                class="px-3 py-1 rounded-md border border-gray-300 text-sm font-medium transition-colors"
                                x-text="page">
                        </button>
                    </template>
                    
                    <button @click="goToPage(pagination.current_page + 1)"
                            :disabled="pagination.current_page >= pagination.last_page"
                            :class="pagination.current_page >= pagination.last_page ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-200'"
                            class="px-3 py-1 rounded-md border border-gray-300 bg-white text-sm font-medium text-gray-700 transition-colors">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </nav>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div x-show="showEditModal" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-50"
         @click.self="closeEditModal()">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">Edit RFID Card</h3>
                    <button @click="closeEditModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form @submit.prevent="updateCard()">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nomor Kartu</label>
                            <input type="text" 
                                   x-model="editForm.card_number" 
                                   disabled
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select x-model="editForm.status" 
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="AVAILABLE">Tersedia</option>
                                <option value="ASSIGNED">Tertugaskan</option>
                                <option value="DAMAGED">Rusak</option>
                                <option value="LOST">Hilang</option>
                                <option value="INACTIVE">Tidak Aktif</option>
                            </select>
                        </div>

                        <div x-show="editForm.status === 'ASSIGNED'">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Karyawan</label>
                            <select x-model="editForm.karyawan_id" 
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Pilih Karyawan</option>
                                <template x-for="employee in availableEmployees" :key="employee.id">
                                    <option :value="employee.id" x-text="`${employee.nama} (${employee.nip}) - ${employee.departemen}`"></option>
                                </template>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Catatan</label>
                            <textarea x-model="editForm.notes" 
                                      rows="3"
                                      class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                      placeholder="Catatan tambahan..."></textarea>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" 
                                @click="closeEditModal()"
                                class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Batal
                        </button>
                        <button type="submit" 
                                :disabled="editLoading"
                                class="px-4 py-2 bg-blue-600 border border-transparent rounded-lg text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50">
                            <span x-show="!editLoading">Simpan</span>
                            <span x-show="editLoading">Menyimpan...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function rfidManagement() {
    return {
        loading: false,
        editLoading: false,
        showEditModal: false,
        cards: [],
        statistics: {},
        availableEmployees: [],
        filters: {
            search: '',
            status: '',
            assigned: ''
        },
        sorting: {
            field: 'created_at',
            direction: 'desc'
        },
        pagination: {
            current_page: 1,
            last_page: 1,
            per_page: 15,
            total: 0,
            from: 0,
            to: 0
        },
        editForm: {
            id: null,
            card_number: '',
            status: 'AVAILABLE',
            karyawan_id: null,
            notes: ''
        },

        init() {
            this.loadStatistics();
            this.loadData();
            this.loadAvailableEmployees();
        },

        async loadStatistics() {
            try {
                console.log('Loading RFID statistics...');
                const url = '{{ route("rfid.statistics") }}';
                console.log('Statistics URL:', url);
                
                const response = await fetch(url, {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });
                
                console.log('Statistics response status:', response.status);
                console.log('Statistics response headers:', [...response.headers.entries()]);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                console.log('Statistics response:', data);
                
                if (data.success) {
                    this.statistics = data.statistics;
                    console.log('Statistics loaded:', this.statistics);
                } else {
                    console.error('Statistics API error:', data.message);
                    this.showNotification('error', data.message || 'Gagal memuat statistik');
                }
            } catch (error) {
                console.error('Error loading statistics:', error);
                this.showNotification('error', 'Gagal memuat statistik: ' + error.message);
            }
        },

        async loadData() {
            this.loading = true;
            try {
                console.log('Loading RFID data...');
                const params = new URLSearchParams({
                    page: this.pagination.current_page,
                    per_page: this.pagination.per_page,
                    sort: this.sorting.field,
                    direction: this.sorting.direction,
                    ...this.filters
                });

                const url = `{{ route("rfid.data") }}?${params}`;
                console.log('Data URL:', url);

                const response = await fetch(url, {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'Cache-Control': 'no-cache',
                        'Pragma': 'no-cache'
                    }
                });
                
                console.log('Data response status:', response.status);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                console.log('Data response:', data);
                
                if (data.success) {
                    this.cards = data.data;
                    this.pagination = data.pagination;
                    console.log('Loaded cards:', this.cards.length, this.cards);
                } else {
                    console.error('Data API error:', data.message);
                    this.showNotification('error', data.message || 'Gagal memuat data RFID');
                }
            } catch (error) {
                console.error('Error loading data:', error);
                this.showNotification('error', 'Gagal memuat data RFID: ' + error.message);
            } finally {
                this.loading = false;
            }
        },

        async loadAvailableEmployees() {
            try {
                const response = await fetch('{{ route("rfid.available-employees") }}', {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });
                const data = await response.json();
                
                if (data.success) {
                    this.availableEmployees = data.data;
                }
            } catch (error) {
                console.error('Error loading available employees:', error);
            }
        },

        sort(field) {
            if (this.sorting.field === field) {
                this.sorting.direction = this.sorting.direction === 'asc' ? 'desc' : 'asc';
            } else {
                this.sorting.field = field;
                this.sorting.direction = 'asc';
            }
            this.pagination.current_page = 1;
            this.loadData();
        },

        goToPage(page) {
            if (page >= 1 && page <= this.pagination.last_page) {
                this.pagination.current_page = page;
                this.loadData();
            }
        },

        getVisiblePages() {
            const current = this.pagination.current_page;
            const last = this.pagination.last_page;
            const delta = 2;
            const pages = [];
            
            const start = Math.max(1, current - delta);
            const end = Math.min(last, current + delta);
            
            for (let i = start; i <= end; i++) {
                pages.push(i);
            }
            
            return pages;
        },

        editCard(card) {
            this.editForm = {
                id: card.id,
                card_number: card.card_number,
                status: card.status,
                karyawan_id: card.karyawan_id,
                notes: card.notes || ''
            };
            this.showEditModal = true;
        },

        closeEditModal() {
            this.showEditModal = false;
            this.editForm = {
                id: null,
                card_number: '',
                status: 'AVAILABLE',
                karyawan_id: null,
                notes: ''
            };
        },

        async updateCard() {
            this.editLoading = true;
            try {
                const response = await fetch(`{{ url('rfid') }}/${this.editForm.id}`, {
                    method: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(this.editForm)
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.showNotification('success', data.message);
                    this.closeEditModal();
                    this.loadData();
                    this.loadStatistics();
                } else {
                    this.showNotification('error', data.message);
                }
            } catch (error) {
                console.error('Error updating card:', error);
                this.showNotification('error', 'Gagal memperbarui kartu RFID');
            } finally {
                this.editLoading = false;
            }
        },

        async deleteCard(card) {
            if (!confirm(`Apakah Anda yakin ingin menghapus kartu RFID ${card.card_number}?`)) {
                return;
            }

            try {
                const response = await fetch(`{{ url('rfid') }}/${card.id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.showNotification('success', data.message);
                    this.loadData();
                    this.loadStatistics();
                } else {
                    this.showNotification('error', data.message);
                }
            } catch (error) {
                console.error('Error deleting card:', error);
                this.showNotification('error', 'Gagal menghapus kartu RFID');
            }
        },

        refreshData() {
            this.loadStatistics();
            this.loadData();
        },

        exportData() {
            // Implement export functionality
            this.showNotification('info', 'Fitur export akan segera tersedia');
        },

        getStatusText(status) {
            const statusTexts = {
                'AVAILABLE': 'Tersedia',
                'ASSIGNED': 'Tertugaskan',
                'DAMAGED': 'Rusak',
                'LOST': 'Hilang',
                'INACTIVE': 'Tidak Aktif'
            };
            return statusTexts[status] || status;
        },

        formatDate(date) {
            if (!date) return '';
            return new Date(date).toLocaleDateString('id-ID', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
        },

        showNotification(type, message) {
            // Use the global notification system
            if (window.showNotification) {
                window.showNotification(type, message);
            } else {
                alert(message);
            }
        }
    }
}
</script>
@endpush