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

                <!-- NIK -->
                <div>
                    <label for="nik" class="block text-sm font-medium text-gray-700 mb-2">
                        NIK Karyawan <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="nik" 
                           id="nik" 
                           x-model="formData.nik"
                           required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg input-focus"
                           :class="errors.nik ? 'border-red-500' : ''"
                           placeholder="Contoh: EMP001">
                    <p x-show="errors.nik" x-text="errors.nik" class="mt-1 text-sm text-red-600"></p>
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
                    <label for="nomor_telepon" class="block text-sm font-medium text-gray-700 mb-2">
                        Nomor Telepon <span class="text-red-500">*</span>
                    </label>
                    <input type="tel" 
                           name="nomor_telepon" 
                           id="nomor_telepon" 
                           x-model="formData.nomor_telepon"
                           required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg input-focus"
                           :class="errors.nomor_telepon ? 'border-red-500' : ''"
                           placeholder="08123456789">
                    <p x-show="errors.nomor_telepon" x-text="errors.nomor_telepon" class="mt-1 text-sm text-red-600"></p>
                </div>

                <!-- Tanggal Lahir -->
                <div>
                    <label for="tanggal_lahir" class="block text-sm font-medium text-gray-700 mb-2">
                        Tanggal Lahir <span class="text-red-500">*</span>
                    </label>
                    <input type="date" 
                           name="tanggal_lahir" 
                           id="tanggal_lahir" 
                           x-model="formData.tanggal_lahir"
                           required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg input-focus"
                           :class="errors.tanggal_lahir ? 'border-red-500' : ''">
                    <p x-show="errors.tanggal_lahir" x-text="errors.tanggal_lahir" class="mt-1 text-sm text-red-600"></p>
                </div>

                <!-- Jenis Kelamin -->
                <div>
                    <label for="jenis_kelamin" class="block text-sm font-medium text-gray-700 mb-2">
                        Jenis Kelamin <span class="text-red-500">*</span>
                    </label>
                    <select name="jenis_kelamin" 
                            id="jenis_kelamin" 
                            x-model="formData.jenis_kelamin"
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg input-focus"
                            :class="errors.jenis_kelamin ? 'border-red-500' : ''">
                        <option value="">Pilih jenis kelamin</option>
                        <option value="L">Laki-laki</option>
                        <option value="P">Perempuan</option>
                    </select>
                    <p x-show="errors.jenis_kelamin" x-text="errors.jenis_kelamin" class="mt-1 text-sm text-red-600"></p>
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
                <!-- Divisi -->
                <div>
                    <label for="divisi" class="block text-sm font-medium text-gray-700 mb-2">
                        Divisi <span class="text-red-500">*</span>
                    </label>
                    <select name="divisi" 
                            id="divisi" 
                            x-model="formData.divisi"
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg input-focus"
                            :class="errors.divisi ? 'border-red-500' : ''">
                        <option value="">Pilih divisi</option>
                        <option value="IT">Information Technology</option>
                        <option value="HR">Human Resources</option>
                        <option value="Finance">Finance</option>
                        <option value="Marketing">Marketing</option>
                        <option value="Operations">Operations</option>
                        <option value="Sales">Sales</option>
                        <option value="Production">Production</option>
                    </select>
                    <p x-show="errors.divisi" x-text="errors.divisi" class="mt-1 text-sm text-red-600"></p>
                </div>

                <!-- Posisi -->
                <div>
                    <label for="posisi" class="block text-sm font-medium text-gray-700 mb-2">
                        Posisi <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="posisi" 
                           id="posisi" 
                           x-model="formData.posisi"
                           required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg input-focus"
                           :class="errors.posisi ? 'border-red-500' : ''"
                           placeholder="Contoh: Software Developer">
                    <p x-show="errors.posisi" x-text="errors.posisi" class="mt-1 text-sm text-red-600"></p>
                </div>

                <!-- Tanggal Bergabung -->
                <div>
                    <label for="tanggal_bergabung" class="block text-sm font-medium text-gray-700 mb-2">
                        Tanggal Bergabung <span class="text-red-500">*</span>
                    </label>
                    <input type="date" 
                           name="tanggal_bergabung" 
                           id="tanggal_bergabung" 
                           x-model="formData.tanggal_bergabung"
                           required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg input-focus"
                           :class="errors.tanggal_bergabung ? 'border-red-500' : ''">
                    <p x-show="errors.tanggal_bergabung" x-text="errors.tanggal_bergabung" class="mt-1 text-sm text-red-600"></p>
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
                        <option value="aktif">Aktif</option>
                        <option value="non_aktif">Non Aktif</option>
                        <option value="cuti">Cuti</option>
                    </select>
                    <p x-show="errors.status" x-text="errors.status" class="mt-1 text-sm text-red-600"></p>
                </div>

                <!-- RFID Card -->
                <div>
                    <label for="rfid_card" class="block text-sm font-medium text-gray-700 mb-2">
                        RFID Card Number
                        <span class="text-sm text-gray-500">(Optional)</span>
                    </label>
                    <input type="text" 
                           name="rfid_card" 
                           id="rfid_card" 
                           x-model="formData.rfid_card"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg input-focus"
                           :class="errors.rfid_card ? 'border-red-500' : ''"
                           placeholder="Nomor kartu RFID">
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
            nik: '',
            email: '',
            nomor_telepon: '',
            tanggal_lahir: '',
            jenis_kelamin: '',
            alamat: '',
            divisi: '',
            posisi: '',
            tanggal_bergabung: new Date().toISOString().split('T')[0], // Default to today
            gaji_pokok: '',
            status: 'aktif', // Default to active
            rfid_card: ''
        },
        errors: {},
        loading: false,
        
        init() {
            // Set default date for tanggal_bergabung
            this.formData.tanggal_bergabung = new Date().toISOString().split('T')[0];
        },
        
        async submitForm() {
            try {
                this.loading = true;
                this.errors = {};
                
                // Prepare form data
                const submitData = { ...this.formData };
                
                // Remove currency formatting from gaji_pokok
                submitData.gaji_pokok = this.parseCurrency(submitData.gaji_pokok);
                
                const response = await fetch('/karyawan/api/store', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': window.CSRF_TOKEN
                    },
                    body: JSON.stringify(submitData)
                });
                
                const data = await response.json();
                
                if (response.ok && data.success) {
                    showNotification('success', 'Karyawan berhasil ditambahkan');
                    // Redirect to karyawan list after 1 second
                    setTimeout(() => {
                        window.location.href = '{{ route("karyawan.index") }}';
                    }, 1000);
                } else {
                    // Handle validation errors
                    if (data.errors) {
                        this.errors = data.errors;
                        showNotification('error', 'Silakan perbaiki kesalahan pada form');
                    } else {
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
                nik: '',
                email: '',
                nomor_telepon: '',
                tanggal_lahir: '',
                jenis_kelamin: '',
                alamat: '',
                divisi: '',
                posisi: '',
                tanggal_bergabung: new Date().toISOString().split('T')[0],
                gaji_pokok: '',
                status: 'aktif',
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
        }
    }
}
</script>
@endpush