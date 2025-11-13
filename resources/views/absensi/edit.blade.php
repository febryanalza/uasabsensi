@extends('layouts.dashboard')

@section('title', 'Edit Absensi')

@section('content')
<div x-data="editAbsensiData()" x-init="init()" class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center space-x-4">
            <a href="{{ route('absensi.index') }}" class="flex items-center text-gray-600 hover:text-gray-900">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Kembali
            </a>
            <div class="h-6 border-l border-gray-300"></div>
            <h1 class="text-2xl font-bold text-gray-900">Edit Data Absensi</h1>
        </div>
        <p class="mt-2 text-sm text-gray-600">Edit data kehadiran karyawan</p>
    </div>

    <!-- Loading State -->
    <div x-show="loadingData" class="text-center py-8">
        <div class="inline-flex items-center px-4 py-2 text-sm text-gray-600">
            <div class="w-4 h-4 spinner mr-2"></div>
            Memuat data absensi...
        </div>
    </div>

    <!-- Form -->
    <form x-show="!loadingData" @submit.prevent="submitForm()" class="bg-white shadow-sm rounded-lg border border-gray-200">
        <div class="p-6 space-y-8">
            <!-- Informasi Karyawan (Read Only) -->
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    Informasi Karyawan
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Karyawan</label>
                        <div class="p-3 bg-gray-50 rounded-lg border border-gray-200">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-r from-blue-600 to-purple-600 flex items-center justify-center">
                                    <span class="text-sm font-medium text-white" x-text="originalData?.karyawan ? getInitials(originalData.karyawan.nama) : ''"></span>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900" x-text="originalData?.karyawan?.nama"></div>
                                    <div class="text-xs text-gray-600">
                                        <span x-text="originalData?.karyawan?.nip || originalData?.karyawan?.nik"></span> • 
                                        <span x-text="originalData?.karyawan?.departemen || 'N/A'"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal</label>
                        <div class="p-3 bg-gray-50 rounded-lg border border-gray-200">
                            <span class="text-sm text-gray-900" x-text="formatDate(originalData?.tanggal)"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informasi Waktu -->
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Informasi Waktu
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Status Kehadiran <span class="text-red-500">*</span>
                        </label>
                        <select 
                            x-model="formData.status"
                            @change="handleStatusChange()"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            :class="errors.status ? 'border-red-500' : ''"
                        >
                            <option value="">Pilih Status</option>
                            <option value="HADIR">Hadir</option>
                            <option value="IZIN">Izin</option>
                            <option value="SAKIT">Sakit</option>
                            <option value="ALPHA">Alpha</option>
                            <option value="CUTI">Cuti</option>
                        </select>
                        <div x-show="errors.status" class="mt-1 text-sm text-red-600" x-text="errors.status?.[0]"></div>
                    </div>

                    <div x-show="formData.status === 'HADIR'">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Jam Masuk
                        </label>
                        <input 
                            type="datetime-local" 
                            x-model="formData.jam_masuk"
                            @change="calculateTardiness()"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            :class="errors.jam_masuk ? 'border-red-500' : ''"
                        >
                        <div x-show="errors.jam_masuk" class="mt-1 text-sm text-red-600" x-text="errors.jam_masuk?.[0]"></div>
                    </div>

                    <div x-show="formData.status === 'HADIR'">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Jam Keluar
                        </label>
                        <input 
                            type="datetime-local" 
                            x-model="formData.jam_keluar"
                            @change="calculateEarlyDeparture()"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            :class="errors.jam_keluar ? 'border-red-500' : ''"
                        >
                        <div x-show="errors.jam_keluar" class="mt-1 text-sm text-red-600" x-text="errors.jam_keluar?.[0]"></div>
                    </div>
                </div>
            </div>

            <!-- Informasi Tambahan -->
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Informasi Tambahan
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Keterangan
                        </label>
                        <textarea 
                            x-model="formData.keterangan"
                            rows="3"
                            placeholder="Keterangan tambahan (opsional)"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            :class="errors.keterangan ? 'border-red-500' : ''"
                        ></textarea>
                        <div x-show="errors.keterangan" class="mt-1 text-sm text-red-600" x-text="errors.keterangan?.[0]"></div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Lokasi
                        </label>
                        <input 
                            type="text" 
                            x-model="formData.lokasi"
                            placeholder="Lokasi absensi (opsional)"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            :class="errors.lokasi ? 'border-red-500' : ''"
                        >
                        <div x-show="errors.lokasi" class="mt-1 text-sm text-red-600" x-text="errors.lokasi?.[0]"></div>
                    </div>
                </div>
            </div>

            <!-- Preview Perhitungan -->
            <div x-show="formData.status === 'HADIR' && formData.jam_masuk && companyRules" class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <h4 class="text-sm font-medium text-yellow-800 mb-3">Preview Perhitungan Ulang</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-gray-600">Jam Masuk Kerja:</span>
                        <span class="font-medium text-gray-900 ml-2" x-text="companyRules?.jam_masuk_kerja || 'N/A'"></span>
                    </div>
                    <div>
                        <span class="text-gray-600">Toleransi Terlambat:</span>
                        <span class="font-medium text-gray-900 ml-2" x-text="(companyRules?.toleransi_terlambat || 0) + ' menit'"></span>
                    </div>
                    <div x-show="calculatedTardiness > 0">
                        <span class="text-red-600">Keterlambatan Baru:</span>
                        <span class="font-medium text-red-700 ml-2" x-text="calculatedTardiness + ' menit'"></span>
                    </div>
                    <div x-show="calculatedTardiness === 0">
                        <span class="text-green-600">Status:</span>
                        <span class="font-medium text-green-700 ml-2">Tepat Waktu</span>
                    </div>
                    <div x-show="originalData?.menit_terlambat > 0">
                        <span class="text-gray-600">Keterlambatan Lama:</span>
                        <span class="font-medium text-gray-700 ml-2" x-text="(originalData?.menit_terlambat || 0) + ' menit'"></span>
                    </div>
                </div>
            </div>

            <!-- Changes Summary -->
            <div x-show="hasChanges()" class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h4 class="text-sm font-medium text-blue-800 mb-3 flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Perubahan yang Akan Disimpan
                </h4>
                <div class="text-sm text-blue-700">
                    <template x-for="change in getChanges()" :key="change.field">
                        <div class="mb-1">
                            <span class="font-medium" x-text="change.label + ':'"></span>
                            <span x-text="change.from + ' → ' + change.to"></span>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3 rounded-b-lg">
            <a href="{{ route('absensi.index') }}" 
               class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                Batal
            </a>
            <button 
                type="submit" 
                :disabled="loading || !hasChanges()"
                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
            >
                <span x-show="!loading">Update Absensi</span>
                <span x-show="loading" class="flex items-center">
                    <div class="w-4 h-4 spinner mr-2"></div>
                    Menyimpan...
                </span>
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function editAbsensiData() {
    return {
        formData: {
            jam_masuk: '',
            jam_keluar: '',
            status: '',
            keterangan: '',
            lokasi: ''
        },
        originalData: null,
        errors: {},
        loading: false,
        loadingData: true,
        companyRules: null,
        calculatedTardiness: 0,
        absensiId: '{{ $id }}',
        
        init() {
            this.loadCompanyRules();
            this.loadAbsensiData();
        },
        
        async loadCompanyRules() {
            try {
                const response = await fetch('/absensi/api/company-rules');
                if (response.ok) {
                    const data = await response.json();
                    if (data.success) {
                        this.companyRules = data.data;
                    }
                }
            } catch (error) {
                console.error('Error loading company rules:', error);
            }
        },
        
        async loadAbsensiData() {
            try {
                this.loadingData = true;
                
                const response = await fetch(`/absensi/api/${this.absensiId}`);
                
                if (response.ok) {
                    const data = await response.json();
                    if (data.success) {
                        this.originalData = data.data;
                        
                        // Populate form with existing data
                        this.formData = {
                            jam_masuk: this.formatDateTimeForInput(this.originalData.jam_masuk),
                            jam_keluar: this.formatDateTimeForInput(this.originalData.jam_keluar),
                            status: this.originalData.status || '',
                            keterangan: this.originalData.keterangan || '',
                            lokasi: this.originalData.lokasi || ''
                        };
                        
                        this.calculateTardiness();
                    } else {
                        showNotification('error', 'Data absensi tidak ditemukan');
                        window.location.href = '{{ route("absensi.index") }}';
                    }
                } else {
                    showNotification('error', 'Gagal memuat data absensi');
                    window.location.href = '{{ route("absensi.index") }}';
                }
            } catch (error) {
                console.error('Error loading absensi data:', error);
                showNotification('error', 'Terjadi kesalahan saat memuat data');
            } finally {
                this.loadingData = false;
            }
        },
        
        handleStatusChange() {
            if (this.formData.status !== 'HADIR') {
                this.formData.jam_masuk = '';
                this.formData.jam_keluar = '';
            }
            this.calculateTardiness();
        },
        
        calculateTardiness() {
            this.calculatedTardiness = 0;
            
            if (this.formData.status === 'HADIR' && this.formData.jam_masuk && this.companyRules && this.originalData) {
                const jamMasukKaryawan = new Date(this.formData.jam_masuk);
                const jamMasukAturan = new Date(this.originalData.tanggal + 'T' + this.companyRules.jam_masuk_kerja);
                
                if (jamMasukKaryawan > jamMasukAturan) {
                    const diffInMinutes = Math.floor((jamMasukKaryawan - jamMasukAturan) / (1000 * 60));
                    this.calculatedTardiness = Math.max(0, diffInMinutes - (this.companyRules.toleransi_terlambat || 0));
                }
            }
        },
        
        calculateEarlyDeparture() {
            // Similar calculation for early departure if needed
            // Implementation depends on company rules for early departure
        },
        
        hasChanges() {
            if (!this.originalData) return false;
            
            const original = {
                jam_masuk: this.formatDateTimeForInput(this.originalData.jam_masuk),
                jam_keluar: this.formatDateTimeForInput(this.originalData.jam_keluar),
                status: this.originalData.status || '',
                keterangan: this.originalData.keterangan || '',
                lokasi: this.originalData.lokasi || ''
            };
            
            return JSON.stringify(this.formData) !== JSON.stringify(original);
        },
        
        getChanges() {
            if (!this.originalData) return [];
            
            const changes = [];
            const fieldLabels = {
                jam_masuk: 'Jam Masuk',
                jam_keluar: 'Jam Keluar',
                status: 'Status',
                keterangan: 'Keterangan',
                lokasi: 'Lokasi'
            };
            
            const original = {
                jam_masuk: this.formatDateTimeForInput(this.originalData.jam_masuk),
                jam_keluar: this.formatDateTimeForInput(this.originalData.jam_keluar),
                status: this.originalData.status || '',
                keterangan: this.originalData.keterangan || '',
                lokasi: this.originalData.lokasi || ''
            };
            
            Object.keys(this.formData).forEach(key => {
                if (this.formData[key] !== original[key]) {
                    changes.push({
                        field: key,
                        label: fieldLabels[key],
                        from: original[key] || '(kosong)',
                        to: this.formData[key] || '(kosong)'
                    });
                }
            });
            
            return changes;
        },
        
        async submitForm() {
            try {
                this.loading = true;
                this.errors = {};
                
                const submitData = { ...this.formData };
                
                // Remove empty values
                Object.keys(submitData).forEach(key => {
                    if (submitData[key] === '') {
                        submitData[key] = null;
                    }
                });
                
                const response = await fetch(`/absensi/api/${this.absensiId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': window.CSRF_TOKEN
                    },
                    body: JSON.stringify(submitData)
                });
                
                const data = await response.json();
                
                if (response.ok && data.success) {
                    showNotification('success', 'Data absensi berhasil diupdate');
                    
                    // Redirect to absensi list after 1 second
                    setTimeout(() => {
                        window.location.href = '{{ route("absensi.index") }}';
                    }, 1000);
                } else {
                    // Handle validation errors
                    if (data.errors) {
                        this.errors = data.errors;
                        showNotification('error', 'Silakan perbaiki kesalahan pada form');
                    } else {
                        showNotification('error', data.message || 'Gagal mengupdate data absensi');
                    }
                }
                
            } catch (error) {
                console.error('Error updating absensi:', error);
                showNotification('error', 'Terjadi kesalahan. Silakan coba lagi.');
            } finally {
                this.loading = false;
            }
        },
        
        formatDate(dateString) {
            if (!dateString) return '-';
            return new Date(dateString).toLocaleDateString('id-ID', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        },
        
        formatDateTimeForInput(dateTimeString) {
            if (!dateTimeString) return '';
            
            const date = new Date(dateTimeString);
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            const hour = String(date.getHours()).padStart(2, '0');
            const minute = String(date.getMinutes()).padStart(2, '0');
            
            return `${year}-${month}-${day}T${hour}:${minute}`;
        },
        
        getInitials(name) {
            if (!name) return 'N/A';
            return name.split(' ').map(word => word.charAt(0)).join('').toUpperCase().substring(0, 2);
        }
    }
}
</script>
@endpush