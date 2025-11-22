@extends('layouts.dashboard')

@section('title', 'Edit RFID Card')

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
        <a href="{{ route('rfid.index') }}" class="ml-1 text-gray-400 hover:text-gray-500 md:ml-2">RFID Management</a>
    </div>
</li>
<li>
    <div class="flex items-center">
        <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
        <span class="ml-1 text-gray-500 md:ml-2">Edit Card</span>
    </div>
</li>
@endsection

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="rfidEdit('{{ $id }}')">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Edit RFID Card</h2>
                <p class="mt-1 text-sm text-gray-600">Perbarui informasi kartu RFID dan penugasan karyawan</p>
            </div>
            <a href="{{ route('rfid.index') }}" 
               class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                Kembali
            </a>
        </div>
    </div>

    <!-- Loading State -->
    <div x-show="loading" class="bg-white rounded-xl shadow-sm border border-gray-200 p-12">
        <div class="flex items-center justify-center">
            <div class="flex items-center space-x-3">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                <span class="text-gray-600">Memuat data kartu RFID...</span>
            </div>
        </div>
    </div>

    <!-- Form -->
    <div x-show="!loading && cardData" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <form @submit.prevent="updateCard()">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Left Column -->
                <div class="space-y-6">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Kartu</h3>
                        
                        <div class="space-y-4">
                            <!-- Card Number -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Nomor Kartu <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                       x-model="form.card_number" 
                                       disabled
                                       class="block w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-600">
                                <p class="mt-1 text-xs text-gray-500">Nomor kartu tidak dapat diubah</p>
                            </div>

                            <!-- Card Type -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Tipe Kartu <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                       x-model="form.card_type" 
                                       disabled
                                       class="block w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-600">
                            </div>

                            <!-- Status -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Status <span class="text-red-500">*</span>
                                </label>
                                <select x-model="form.status" 
                                        @change="onStatusChange()"
                                        class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="AVAILABLE">Tersedia</option>
                                    <option value="ASSIGNED">Tertugaskan</option>
                                    <option value="DAMAGED">Rusak</option>
                                    <option value="LOST">Hilang</option>
                                    <option value="INACTIVE">Tidak Aktif</option>
                                </select>
                            </div>

                            <!-- Notes -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Catatan
                                </label>
                                <textarea x-model="form.notes" 
                                          rows="4"
                                          class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                          placeholder="Catatan tambahan tentang kartu ini..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-6">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Penugasan Karyawan</h3>
                        
                        <div class="space-y-4">
                            <!-- Employee Assignment (only shown if status is ASSIGNED) -->
                            <div x-show="form.status === 'ASSIGNED'">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Pilih Karyawan <span class="text-red-500">*</span>
                                </label>
                                <select x-model="form.karyawan_id" 
                                        class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Pilih Karyawan</option>
                                    <template x-for="employee in availableEmployees" :key="employee.id">
                                        <option :value="employee.id" 
                                                x-text="`${employee.nama} (${employee.nip}) - ${employee.departemen}`">
                                        </option>
                                    </template>
                                </select>
                                <p class="mt-1 text-xs text-gray-500">
                                    Pilih karyawan yang akan menggunakan kartu ini
                                </p>
                            </div>

                            <!-- Current Assignment Info -->
                            <div x-show="cardData && cardData.karyawan" class="bg-blue-50 rounded-lg p-4">
                                <h4 class="text-sm font-medium text-blue-900 mb-2">Informasi Penugasan Saat Ini</h4>
                                <div class="text-sm text-blue-800">
                                    <p><strong>Nama:</strong> <span x-text="cardData.karyawan?.nama"></span></p>
                                    <p><strong>NIP:</strong> <span x-text="cardData.karyawan?.nip"></span></p>
                                    <p><strong>Departemen:</strong> <span x-text="cardData.karyawan?.departemen"></span></p>
                                    <p><strong>Jabatan:</strong> <span x-text="cardData.karyawan?.jabatan"></span></p>
                                    <p x-show="cardData.assigned_at">
                                        <strong>Ditugaskan:</strong> 
                                        <span x-text="formatDate(cardData.assigned_at)"></span>
                                    </p>
                                </div>
                            </div>

                            <!-- Warning for status change -->
                            <div x-show="form.status !== 'ASSIGNED' && cardData && cardData.karyawan" 
                                 class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-yellow-800">Peringatan</h3>
                                        <div class="mt-2 text-sm text-yellow-700">
                                            <p>Mengubah status ke non-ASSIGNED akan menghapus penugasan karyawan saat ini.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card History -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h4 class="text-sm font-medium text-gray-900 mb-3">Informasi Kartu</h4>
                        <div class="space-y-2 text-sm text-gray-600">
                            <div class="flex justify-between">
                                <span>Dibuat:</span>
                                <span x-text="formatDate(cardData?.created_at)"></span>
                            </div>
                            <div class="flex justify-between">
                                <span>Diperbarui:</span>
                                <span x-text="formatDate(cardData?.updated_at)"></span>
                            </div>
                            <div class="flex justify-between" x-show="cardData?.assigned_at">
                                <span>Tanggal Tugaskan:</span>
                                <span x-text="formatDate(cardData?.assigned_at)"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex justify-end space-x-3 mt-8 pt-6 border-t border-gray-200">
                <a href="{{ route('rfid.index') }}" 
                   class="px-6 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors">
                    Batal
                </a>
                <button type="submit" 
                        :disabled="submitting"
                        class="px-6 py-2 bg-blue-600 border border-transparent rounded-lg text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 transition-colors">
                    <span x-show="!submitting" class="flex items-center">
                        <i class="fas fa-save mr-2"></i>
                        Simpan Perubahan
                    </span>
                    <span x-show="submitting" class="flex items-center">
                        <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
                        Menyimpan...
                    </span>
                </button>
            </div>
        </form>
    </div>

    <!-- Error State -->
    <div x-show="!loading && !cardData" class="bg-white rounded-xl shadow-sm border border-gray-200 p-12">
        <div class="text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-red-100 rounded-full mb-4">
                <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Kartu RFID Tidak Ditemukan</h3>
            <p class="text-gray-500 mb-4">Kartu RFID yang Anda cari tidak dapat ditemukan dalam sistem.</p>
            <a href="{{ route('rfid.index') }}" 
               class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg text-sm font-medium text-white hover:bg-blue-700">
                <i class="fas fa-arrow-left mr-2"></i>
                Kembali ke Daftar RFID
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function rfidEdit(cardId) {
    return {
        loading: true,
        submitting: false,
        cardData: null,
        availableEmployees: [],
        form: {
            status: 'AVAILABLE',
            karyawan_id: null,
            notes: ''
        },

        init() {
            this.loadCardData();
            this.loadAvailableEmployees();
        },

        async loadCardData() {
            try {
                const response = await fetch(`{{ url('rfid') }}/${cardId}`, {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.cardData = data.data;
                    this.form = {
                        card_number: data.data.card_number,
                        card_type: data.data.card_type,
                        status: data.data.status,
                        karyawan_id: data.data.karyawan_id,
                        notes: data.data.notes || ''
                    };
                } else {
                    this.showNotification('error', 'Kartu RFID tidak ditemukan');
                }
            } catch (error) {
                console.error('Error loading card data:', error);
                this.showNotification('error', 'Gagal memuat data kartu RFID');
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
                    // Include current assigned employee if exists
                    this.availableEmployees = data.data;
                    if (this.cardData && this.cardData.karyawan) {
                        // Add current employee to available list if not already there
                        const currentEmployee = this.cardData.karyawan;
                        const exists = this.availableEmployees.find(emp => emp.id === currentEmployee.id);
                        if (!exists) {
                            this.availableEmployees.unshift(currentEmployee);
                        }
                    }
                }
            } catch (error) {
                console.error('Error loading available employees:', error);
            }
        },

        onStatusChange() {
            if (this.form.status !== 'ASSIGNED') {
                this.form.karyawan_id = null;
            }
        },

        async updateCard() {
            this.submitting = true;
            
            try {
                // Validation
                if (this.form.status === 'ASSIGNED' && !this.form.karyawan_id) {
                    this.showNotification('error', 'Pilih karyawan untuk kartu yang tertugaskan');
                    return;
                }

                const response = await fetch(`{{ url('rfid') }}/${cardId}`, {
                    method: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(this.form)
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.showNotification('success', data.message);
                    // Redirect to index page after success
                    setTimeout(() => {
                        window.location.href = '{{ route("rfid.index") }}';
                    }, 1500);
                } else {
                    this.showNotification('error', data.message);
                    if (data.errors) {
                        Object.values(data.errors).forEach(errorArray => {
                            errorArray.forEach(error => {
                                this.showNotification('error', error);
                            });
                        });
                    }
                }
            } catch (error) {
                console.error('Error updating card:', error);
                this.showNotification('error', 'Gagal memperbarui kartu RFID');
            } finally {
                this.submitting = false;
            }
        },

        formatDate(date) {
            if (!date) return '-';
            return new Date(date).toLocaleDateString('id-ID', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
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