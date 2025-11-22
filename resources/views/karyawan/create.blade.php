@extends('layouts.dashboard')

@section('title', 'Tambah Karyawan')
@section('page-title', 'Tambah Karyawan')

@section('breadcrumb')
<li class="inline-flex items-center">
    <a href="{{ route('dashboard') }}" class="text-gray-500 hover:text-gray-700">
        <i class="fas fa-home mr-2"></i>
        Dashboard
    </a>
</li>
<li class="inline-flex items-center">
    <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
    <a href="{{ route('karyawan.index') }}" class="text-gray-500 hover:text-gray-700">
        Management Karyawan
    </a>
</li>
<li class="inline-flex items-center">
    <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
    <span class="text-gray-700 font-medium">Tambah Karyawan</span>
</li>
@endsection

@push('meta')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
<div x-data="createKaryawanData()">
    <form @submit.prevent="submitForm()" class="space-y-6">
        <!-- Personal Information -->
        <div class="bg-white rounded-xl p-6 card-shadow">
            <div class="border-b border-gray-200 pb-4 mb-6">
                <h3 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-user mr-2 text-blue-600"></i>
                    Informasi Personal
                </h3>
                <p class="text-sm text-gray-600 mt-1">Data pribadi karyawan</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Nama -->
                <div>
                    <label for="nama" class="block text-sm font-medium text-gray-700 mb-2">
                        Nama Lengkap <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="nama" 
                           id="nama" 
                           x-model="formData.nama"
                           required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg input-focus"
                           :class="errors.nama ? 'border-red-500' : ''"
                           placeholder="Masukkan nama lengkap">
                    <p x-show="errors.nama" x-text="errors.nama" class="mt-1 text-sm text-red-600"></p>
                </div>

                <!-- NIP -->
                <div>
                    <label for="nip" class="block text-sm font-medium text-gray-700 mb-2">
                        NIP Karyawan <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="nip" 
                           id="nip" 
                           x-model="formData.nip"
                           required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg input-focus"
                           :class="errors.nip ? 'border-red-500' : ''"
                           placeholder="Contoh: EMP001">
                    <p x-show="errors.nip" x-text="errors.nip" class="mt-1 text-sm text-red-600"></p>
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        Email <span class="text-red-500">*</span>
                    </label>
                    <input type="email" 
                           name="email" 
                           id="email" 
                           x-model="formData.email"
                           required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg input-focus"
                           :class="errors.email ? 'border-red-500' : ''"
                           placeholder="nama@company.com">
                    <p x-show="errors.email" x-text="errors.email" class="mt-1 text-sm text-red-600"></p>
                </div>

                <!-- Nomor Telepon -->
                <div>
                    <label for="telepon" class="block text-sm font-medium text-gray-700 mb-2">
                        Nomor Telepon <span class="text-red-500">*</span>
                    </label>
                    <input type="tel" 
                           name="telepon" 
                           id="telepon" 
                           x-model="formData.telepon"
                           required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg input-focus"
                           :class="errors.telepon ? 'border-red-500' : ''"
                           placeholder="08123456789">
                    <p x-show="errors.telepon" x-text="errors.telepon" class="mt-1 text-sm text-red-600"></p>
                </div>





                <!-- Alamat -->
                <div class="md:col-span-2">
                    <label for="alamat" class="block text-sm font-medium text-gray-700 mb-2">
                        Alamat <span class="text-red-500">*</span>
                    </label>
                    <textarea name="alamat" 
                              id="alamat" 
                              x-model="formData.alamat"
                              rows="3"
                              required
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg input-focus resize-none"
                              :class="errors.alamat ? 'border-red-500' : ''"
                              placeholder="Masukkan alamat lengkap"></textarea>
                    <p x-show="errors.alamat" x-text="errors.alamat" class="mt-1 text-sm text-red-600"></p>
                </div>
            </div>
        </div>

        <!-- Employment Information -->
        <div class="bg-white rounded-xl p-6 card-shadow">
            <div class="border-b border-gray-200 pb-4 mb-6">
                <h3 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-briefcase mr-2 text-green-600"></i>
                    Informasi Pekerjaan
                </h3>
                <p class="text-sm text-gray-600 mt-1">Data kepegawaian dan posisi</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Departemen -->
                <div>
                    <label for="departemen" class="block text-sm font-medium text-gray-700 mb-2">
                        Departemen <span class="text-red-500">*</span>
                    </label>
                    <select name="departemen" 
                            id="departemen" 
                            x-model="formData.departemen"
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg input-focus"
                            :class="errors.departemen ? 'border-red-500' : ''">
                        <option value="">Pilih departemen</option>
                        <option value="IT">Information Technology</option>
                        <option value="HR">Human Resources</option>
                        <option value="Finance">Finance</option>
                        <option value="Marketing">Marketing</option>
                        <option value="Operations">Operations</option>
                        <option value="Sales">Sales</option>
                        <option value="Production">Production</option>
                    </select>
                    <p x-show="errors.departemen" x-text="errors.departemen" class="mt-1 text-sm text-red-600"></p>
                </div>

                <!-- Jabatan -->
                <div>
                    <label for="jabatan" class="block text-sm font-medium text-gray-700 mb-2">
                        Jabatan <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="jabatan" 
                           id="jabatan" 
                           x-model="formData.jabatan"
                           required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg input-focus"
                           :class="errors.jabatan ? 'border-red-500' : ''"
                           placeholder="Contoh: Software Developer">
                    <p x-show="errors.jabatan" x-text="errors.jabatan" class="mt-1 text-sm text-red-600"></p>
                </div>

                <!-- Tanggal Masuk -->
                <div>
                    <label for="tanggal_masuk" class="block text-sm font-medium text-gray-700 mb-2">
                        Tanggal Masuk <span class="text-red-500">*</span>
                    </label>
                    <input type="date" 
                           name="tanggal_masuk" 
                           id="tanggal_masuk" 
                           x-model="formData.tanggal_masuk"
                           required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg input-focus"
                           :class="errors.tanggal_masuk ? 'border-red-500' : ''">
                    <p x-show="errors.tanggal_masuk" x-text="errors.tanggal_masuk" class="mt-1 text-sm text-red-600"></p>
                </div>

                <!-- Gaji Pokok -->
                <div>
                    <label for="gaji_pokok" class="block text-sm font-medium text-gray-700 mb-2">
                        Gaji Pokok <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">Rp</span>
                        <input type="text" 
                               name="gaji_pokok" 
                               id="gaji_pokok" 
                               x-model="formData.gaji_pokok"
                               @input="formatCurrency($event)"
                               required
                               class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg input-focus"
                               :class="errors.gaji_pokok ? 'border-red-500' : ''"
                               placeholder="5,000,000">
                    </div>
                    <p x-show="errors.gaji_pokok" x-text="errors.gaji_pokok" class="mt-1 text-sm text-red-600"></p>
                </div>

                <!-- Status -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                        Status Karyawan <span class="text-red-500">*</span>
                    </label>
                    <select name="status" 
                            id="status" 
                            x-model="formData.status"
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg input-focus"
                            :class="errors.status ? 'border-red-500' : ''">
                        <option value="">Pilih status</option>
                        <option value="AKTIF">Aktif</option>
                        <option value="CUTI">Cuti</option>
                        <option value="RESIGN">Resign</option>
                    </select>
                    <p x-show="errors.status" x-text="errors.status" class="mt-1 text-sm text-red-600"></p>
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        Password Login <span class="text-red-500">*</span>
                    </label>
                    <input type="password" 
                           name="password" 
                           id="password" 
                           x-model="formData.password"
                           required
                           minlength="8"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg input-focus"
                           :class="errors.password ? 'border-red-500' : ''"
                           placeholder="Minimal 8 karakter">
                    <p x-show="errors.password" x-text="errors.password" class="mt-1 text-sm text-red-600"></p>
                </div>

                <!-- Role -->
                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700 mb-2">
                        Role Akses
                    </label>
                    <select name="role" 
                            id="role" 
                            x-model="formData.role"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg input-focus"
                            :class="errors.role ? 'border-red-500' : ''">
                        <option value="USER">User</option>
                        <option value="MANAGER">Manager</option>
                        <option value="ADMIN">Admin</option>
                    </select>
                    <p x-show="errors.role" x-text="errors.role" class="mt-1 text-sm text-red-600"></p>
                </div>

                <!-- RFID Card -->
                <div class="md:col-span-2">
                    <label for="rfid_card" class="block text-sm font-medium text-gray-700 mb-2">
                        RFID Card Number
                        <span class="text-sm text-gray-500">(Optional)</span>
                    </label>
                    <div class="relative">
                        <select name="rfid_card" 
                                id="rfid_card" 
                                x-model="formData.rfid_card"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg input-focus"
                                :class="errors.rfid_card ? 'border-red-500' : ''">
                            <option value="">Pilih kartu RFID (opsional)</option>
                            <template x-for="rfid in availableRfidCards" :key="rfid.id">
                                <option :value="rfid.card_number" 
                                        x-text="rfid.card_number + ' (' + rfid.card_type + ')'">
                                </option>
                            </template>
                        </select>
                        <div x-show="loadingRfidCards" class="absolute right-3 top-3">
                            <div class="w-5 h-5 spinner"></div>
                        </div>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Kartu RFID akan digunakan untuk sistem absensi</p>
                    <p x-show="errors.rfid_card" x-text="errors.rfid_card" class="mt-1 text-sm text-red-600"></p>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center justify-between bg-white rounded-xl p-6 card-shadow">
            <a href="{{ route('karyawan.index') }}" 
               class="px-6 py-3 text-gray-600 hover:text-gray-800 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                Kembali
            </a>

            <div class="flex items-center space-x-4">
                <button type="button" 
                        @click="resetForm()"
                        class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    <i class="fas fa-undo mr-2"></i>
                    Reset
                </button>

                <button type="submit" 
                        :disabled="loading"
                        class="btn-primary text-white px-6 py-3 rounded-lg font-medium hover:shadow-lg transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span x-show="!loading" class="flex items-center">
                        <i class="fas fa-save mr-2"></i>
                        Simpan Karyawan
                    </span>
                    <span x-show="loading" class="flex items-center">
                        <div class="w-4 h-4 spinner mr-2"></div>
                        Menyimpan...
                    </span>
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function createKaryawanData() {
    return {
        formData: {
            nama: '',
            nip: '',
            email: '',
            telepon: '',
            alamat: '',
            departemen: '',
            jabatan: '',
            tanggal_masuk: new Date().toISOString().split('T')[0], // Default to today
            gaji_pokok: '',
            status: 'AKTIF', // Default to active
            password: '',
            role: 'USER', // Default role
            rfid_card: ''
        },
        errors: {},
        loading: false,
        availableRfidCards: [],
        loadingRfidCards: false,
        
        init() {
            // Set default date for tanggal_masuk
            this.formData.tanggal_masuk = new Date().toISOString().split('T')[0];
            
            // Load available RFID cards
            this.loadAvailableRfidCards();
        },
        
        async loadAvailableRfidCards() {
            this.loadingRfidCards = true;
            try {
                const response = await fetch('/karyawan/api/available-rfid');
                const data = await response.json();
                
                if (data.success) {
                    this.availableRfidCards = data.data;
                } else {
                    console.error('Failed to load RFID cards:', data.message);
                    showNotification('warning', 'Gagal memuat data kartu RFID');
                }
            } catch (error) {
                console.error('Error loading RFID cards:', error);
                showNotification('error', 'Terjadi kesalahan saat memuat kartu RFID');
            } finally {
                this.loadingRfidCards = false;
            }
        },
        
        async submitForm() {
            try {
                this.loading = true;
                this.errors = {};
                
                // Prepare form data
                const submitData = { ...this.formData };
                
                // Map rfid_card to rfid_card_number for backend compatibility
                if (submitData.rfid_card) {
                    submitData.rfid_card_number = submitData.rfid_card;
                }
                
                // Remove currency formatting from gaji_pokok
                submitData.gaji_pokok = this.parseCurrency(submitData.gaji_pokok);
                
                console.log('Submitting data:', submitData);
                
                const response = await fetch('/karyawan/store', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(submitData)
                });
                
                console.log('Response status:', response.status);
                
                const data = await response.json();
                console.log('Response data:', data);
                
                if (response.ok && data.success) {
                    showNotification('success', 'Karyawan berhasil ditambahkan');
                    
                    // Trigger real-time update untuk semua tab yang terbuka
                    this.triggerRealTimeUpdate(data.data);
                    
                    // Reset form
                    this.resetForm();
                    
                    // Optional: Redirect to karyawan list atau tetap di form untuk input berikutnya
                    const shouldRedirect = confirm('Karyawan berhasil ditambahkan! Apakah Anda ingin kembali ke daftar karyawan?');
                    if (shouldRedirect) {
                        window.location.href = '{{ route("karyawan.index") }}';
                    }
                } else {
                    // Handle validation errors
                    if (data.errors) {
                        this.errors = data.errors;
                        console.log('Validation errors:', data.errors);
                        showNotification('error', 'Silakan perbaiki kesalahan pada form');
                    } else {
                        console.log('Error message:', data.message);
                        showNotification('error', data.message || 'Gagal menambahkan karyawan');
                    }
                }
                
            } catch (error) {
                console.error('Error creating karyawan:', error);
                showNotification('error', 'Terjadi kesalahan. Silakan coba lagi.');
            } finally {
                this.loading = false;
            }
        },
        
        resetForm() {
            this.formData = {
                nama: '',
                nip: '',
                email: '',
                telepon: '',
                alamat: '',
                departemen: '',
                jabatan: '',
                tanggal_masuk: new Date().toISOString().split('T')[0],
                gaji_pokok: '',
                status: 'AKTIF',
                password: '',
                role: 'USER',
                rfid_card: ''
            };
            this.errors = {};
        },
        
        formatCurrency(event) {
            let value = event.target.value.replace(/[^\d]/g, '');
            if (value) {
                value = parseInt(value).toLocaleString('id-ID');
            }
            event.target.value = value;
            this.formData.gaji_pokok = value;
        },
        
        parseCurrency(value) {
            if (!value) return 0;
            return parseInt(value.replace(/[^\d]/g, '')) || 0;
        },
        
        // Real-time update methods
        triggerRealTimeUpdate(newKaryawan) {
            // Use the global real-time notification system
            if (window.realTimeNotifications) {
                window.realTimeNotifications.broadcast('create', newKaryawan);
            } else {
                // Fallback untuk backward compatibility
                localStorage.setItem('karyawan_update', JSON.stringify({
                    action: 'create',
                    data: newKaryawan,
                    timestamp: new Date().getTime()
                }));
                
                window.dispatchEvent(new CustomEvent('karyawanUpdated', {
                    detail: {
                        action: 'create',
                        data: newKaryawan
                    }
                }));
            }
            
            console.log('✅ Real-time update triggered for new karyawan:', newKaryawan);
        }
    }
}
</script>
@endpush