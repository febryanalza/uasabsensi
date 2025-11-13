@extends('layouts.dashboard')

@section('title', 'Edit Gaji')
@section('page-title', 'Edit Data Gaji')

@section('breadcrumb')
<li class="inline-flex items-center">
    <a href="{{ route('dashboard') }}" class="text-gray-500 hover:text-gray-700">
        <i class="fas fa-home mr-2"></i>
        Dashboard
    </a>
</li>
<li class="inline-flex items-center">
    <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
    <a href="{{ route('gaji.index') }}" class="text-gray-500 hover:text-gray-700">
        Management Gaji
    </a>
</li>
<li class="inline-flex items-center">
    <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
    <span class="text-gray-700 font-medium">Edit Gaji</span>
</li>
@endsection

@section('content')
<div class="space-y-6" x-data="gajiEditData()">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div class="flex items-center space-x-3">
            <button onclick="window.history.back()" 
                    class="p-2 border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50 transition-colors">
                <i class="fas fa-arrow-left"></i>
            </button>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Edit Data Gaji</h1>
                <p class="text-gray-600" x-text="gajiData?.karyawan?.nama ? `${gajiData.karyawan.nama} - ${gajiData.period_display}` : 'Loading...'"></p>
            </div>
        </div>
        
        <div class="flex space-x-2">
            <button @click="recalculateGaji()" 
                    :disabled="loading"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 transition-colors">
                <i class="fas fa-calculator mr-2"></i>
                Hitung Ulang
            </button>
            <button @click="saveChanges()" 
                    :disabled="loading || saving"
                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 transition-colors">
                <span x-show="!saving">Simpan Perubahan</span>
                <span x-show="saving">Menyimpan...</span>
            </button>
        </div>
    </div>

    <form @submit.prevent="saveChanges()">
        <!-- Employee Information (Read-only) -->
        <div class="bg-white rounded-xl p-6 card-shadow">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Karyawan</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="flex items-center space-x-4">
                    <div class="flex-shrink-0">
                        <div class="h-16 w-16 bg-gradient-to-r from-blue-600 to-purple-600 rounded-full flex items-center justify-center">
                            <span class="text-xl font-bold text-white" x-text="getInitials(gajiData?.karyawan?.nama)"></span>
                        </div>
                    </div>
                    <div class="flex-1">
                        <h2 class="text-xl font-bold text-gray-900" x-text="gajiData?.karyawan?.nama"></h2>
                        <p class="text-gray-600" x-text="gajiData?.karyawan?.jabatan"></p>
                    </div>
                </div>
                
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">NIP:</span>
                        <span class="font-medium" x-text="gajiData?.karyawan?.nip"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Departemen:</span>
                        <span class="font-medium" x-text="gajiData?.karyawan?.departemen"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Status:</span>
                        <span class="font-medium" x-text="gajiData?.karyawan?.status"></span>
                    </div>
                </div>
                
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Periode:</span>
                        <span class="font-medium" x-text="gajiData?.period_display"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Status Gaji:</span>
                        <span class="px-2 py-1 text-xs rounded-full"
                              :class="gajiData?.status === 'dibayar' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'"
                              x-text="gajiData?.status"></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Salary Components -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Income Section -->
            <div class="bg-white rounded-xl p-6 card-shadow">
                <div class="flex items-center border-b border-gray-200 pb-4 mb-4">
                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-plus text-green-600"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">PENDAPATAN</h3>
                </div>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Gaji Pokok</label>
                        <input type="number" 
                               x-model="formData.gaji_pokok"
                               @input="calculateTotals()"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 input-focus" 
                               step="1000"
                               min="0">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tunjangan Jabatan</label>
                        <input type="number" 
                               x-model="formData.tunjangan_jabatan"
                               @input="calculateTotals()"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 input-focus" 
                               step="1000"
                               min="0">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tunjangan Transport</label>
                        <input type="number" 
                               x-model="formData.tunjangan_transport"
                               @input="calculateTotals()"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 input-focus" 
                               step="1000"
                               min="0">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tunjangan Makan</label>
                        <input type="number" 
                               x-model="formData.tunjangan_makan"
                               @input="calculateTotals()"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 input-focus" 
                               step="1000"
                               min="0">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tunjangan Lembur</label>
                        <input type="number" 
                               x-model="formData.tunjangan_lembur"
                               @input="calculateTotals()"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 input-focus" 
                               step="1000"
                               min="0">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Bonus Kehadiran</label>
                        <input type="number" 
                               x-model="formData.bonus_kehadiran"
                               @input="calculateTotals()"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 input-focus" 
                               step="1000"
                               min="0">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Bonus KPI</label>
                        <input type="number" 
                               x-model="formData.bonus_kpi"
                               @input="calculateTotals()"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 input-focus" 
                               step="1000"
                               min="0">
                    </div>
                    
                    <hr class="my-4">
                    <div class="bg-green-50 p-4 rounded-lg">
                        <div class="flex justify-between items-center">
                            <span class="text-lg font-semibold text-green-700">Total Pendapatan</span>
                            <span class="text-lg font-bold text-green-700" x-text="formatCurrency(calculatedTotals.total_pendapatan)"></span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Deductions Section -->
            <div class="bg-white rounded-xl p-6 card-shadow">
                <div class="flex items-center border-b border-gray-200 pb-4 mb-4">
                    <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-minus text-red-600"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">POTONGAN</h3>
                </div>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Potongan Terlambat</label>
                        <input type="number" 
                               x-model="formData.potongan_terlambat"
                               @input="calculateTotals()"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 input-focus" 
                               step="1000"
                               min="0">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Potongan Alpha</label>
                        <input type="number" 
                               x-model="formData.potongan_alpha"
                               @input="calculateTotals()"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 input-focus" 
                               step="1000"
                               min="0">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">BPJS Kesehatan (1%)</label>
                        <input type="number" 
                               x-model="formData.bpjs_kesehatan"
                               @input="calculateTotals()"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 input-focus" 
                               step="1000"
                               min="0">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">BPJS Ketenagakerjaan (2%)</label>
                        <input type="number" 
                               x-model="formData.bpjs_ketenagakerjaan"
                               @input="calculateTotals()"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 input-focus" 
                               step="1000"
                               min="0">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">PPh21</label>
                        <input type="number" 
                               x-model="formData.pph21"
                               @input="calculateTotals()"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 input-focus" 
                               step="1000"
                               min="0">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Potongan Lainnya</label>
                        <input type="number" 
                               x-model="formData.potongan_lainnya"
                               @input="calculateTotals()"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 input-focus" 
                               step="1000"
                               min="0">
                    </div>
                    
                    <hr class="my-4">
                    <div class="bg-red-50 p-4 rounded-lg">
                        <div class="flex justify-between items-center">
                            <span class="text-lg font-semibold text-red-700">Total Potongan</span>
                            <span class="text-lg font-bold text-red-700" x-text="formatCurrency(calculatedTotals.total_potongan)"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Final Salary & Attendance Summary -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Final Salary -->
            <div class="bg-white rounded-xl p-6 card-shadow">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Gaji Bersih</h3>
                
                <div class="bg-gradient-to-r from-purple-600 to-blue-600 rounded-xl p-6 text-white">
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="text-xl font-bold">GAJI BERSIH</h3>
                            <p class="text-purple-100 text-sm">Total Pendapatan - Total Potongan</p>
                        </div>
                        <div class="text-right">
                            <div class="text-3xl font-bold" x-text="formatCurrency(calculatedTotals.gaji_bersih)"></div>
                        </div>
                    </div>
                </div>
                
                <!-- Status -->
                <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status Pembayaran</label>
                    <select x-model="formData.status" 
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 input-focus">
                        <option value="pending">Pending</option>
                        <option value="dibayar">Dibayar</option>
                        <option value="ditunda">Ditunda</option>
                    </select>
                </div>
                
                <!-- Notes -->
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Catatan</label>
                    <textarea x-model="formData.catatan" 
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 input-focus" 
                              rows="3" 
                              placeholder="Catatan tambahan..."></textarea>
                </div>
            </div>
            
            <!-- Attendance Summary (Read-only) -->
            <div class="bg-white rounded-xl p-6 card-shadow">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Ringkasan Kehadiran</h3>
                
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-gray-50 rounded-lg p-4 text-center">
                        <div class="text-2xl font-bold text-green-600" x-text="gajiData?.jumlah_hadir || 0"></div>
                        <div class="text-sm text-gray-600">Hadir</div>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4 text-center">
                        <div class="text-2xl font-bold text-yellow-600" x-text="gajiData?.jumlah_izin || 0"></div>
                        <div class="text-sm text-gray-600">Izin</div>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4 text-center">
                        <div class="text-2xl font-bold text-blue-600" x-text="gajiData?.jumlah_sakit || 0"></div>
                        <div class="text-sm text-gray-600">Sakit</div>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4 text-center">
                        <div class="text-2xl font-bold text-red-600" x-text="gajiData?.jumlah_alpha || 0"></div>
                        <div class="text-sm text-gray-600">Alpha</div>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4 text-center">
                        <div class="text-2xl font-bold text-orange-600" x-text="gajiData?.jumlah_terlambat || 0"></div>
                        <div class="text-sm text-gray-600">Terlambat</div>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4 text-center">
                        <div class="text-2xl font-bold text-purple-600" x-text="gajiData?.total_jam_lembur || 0"></div>
                        <div class="text-sm text-gray-600">Jam Lembur</div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function gajiEditData() {
    return {
        gajiData: null,
        formData: {},
        calculatedTotals: {
            total_pendapatan: 0,
            total_potongan: 0,
            gaji_bersih: 0
        },
        loading: true,
        saving: false,
        
        async init() {
            await this.loadGajiData();
        },
        
        async loadGajiData() {
            const gajiId = this.getGajiId();
            if (!gajiId) {
                showNotification('error', 'ID gaji tidak valid');
                window.history.back();
                return;
            }
            
            try {
                const response = await fetch(`/gaji/${gajiId}/detail`);
                if (response.ok) {
                    const data = await response.json();
                    if (data.success) {
                        this.gajiData = data.data;
                        this.initFormData();
                        this.calculateTotals();
                    } else {
                        showNotification('error', data.message || 'Gagal memuat data gaji');
                        window.history.back();
                    }
                } else {
                    showNotification('error', 'Gagal memuat data gaji');
                    window.history.back();
                }
            } catch (error) {
                console.error('Error loading gaji data:', error);
                showNotification('error', 'Gagal memuat data gaji');
                window.history.back();
            } finally {
                this.loading = false;
            }
        },
        
        initFormData() {
            this.formData = {
                gaji_pokok: parseFloat(this.gajiData.gaji_pokok) || 0,
                tunjangan_jabatan: parseFloat(this.gajiData.tunjangan_jabatan) || 0,
                tunjangan_transport: parseFloat(this.gajiData.tunjangan_transport) || 0,
                tunjangan_makan: parseFloat(this.gajiData.tunjangan_makan) || 0,
                tunjangan_lembur: parseFloat(this.gajiData.tunjangan_lembur) || 0,
                bonus_kehadiran: parseFloat(this.gajiData.bonus_kehadiran) || 0,
                bonus_kpi: parseFloat(this.gajiData.bonus_kpi) || 0,
                potongan_terlambat: parseFloat(this.gajiData.potongan_terlambat) || 0,
                potongan_alpha: parseFloat(this.gajiData.potongan_alpha) || 0,
                bpjs_kesehatan: parseFloat(this.gajiData.bpjs_kesehatan) || 0,
                bpjs_ketenagakerjaan: parseFloat(this.gajiData.bpjs_ketenagakerjaan) || 0,
                pph21: parseFloat(this.gajiData.pph21) || 0,
                potongan_lainnya: parseFloat(this.gajiData.potongan_lainnya) || 0,
                status: this.gajiData.status || 'pending',
                catatan: this.gajiData.catatan || ''
            };
        },
        
        calculateTotals() {
            const income = [
                'gaji_pokok', 'tunjangan_jabatan', 'tunjangan_transport',
                'tunjangan_makan', 'tunjangan_lembur', 'bonus_kehadiran', 'bonus_kpi'
            ];
            
            const deductions = [
                'potongan_terlambat', 'potongan_alpha', 'bpjs_kesehatan',
                'bpjs_ketenagakerjaan', 'pph21', 'potongan_lainnya'
            ];
            
            this.calculatedTotals.total_pendapatan = income.reduce((total, field) => 
                total + (parseFloat(this.formData[field]) || 0), 0
            );
            
            this.calculatedTotals.total_potongan = deductions.reduce((total, field) => 
                total + (parseFloat(this.formData[field]) || 0), 0
            );
            
            this.calculatedTotals.gaji_bersih = 
                this.calculatedTotals.total_pendapatan - this.calculatedTotals.total_potongan;
        },
        
        async recalculateGaji() {
            try {
                this.loading = true;
                
                const response = await fetch('/gaji/api/calculate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': window.CSRF_TOKEN
                    },
                    body: JSON.stringify({
                        karyawan_id: this.gajiData.karyawan_id,
                        bulan: this.gajiData.bulan,
                        tahun: this.gajiData.tahun
                    })
                });
                
                if (response.ok) {
                    const data = await response.json();
                    if (data.success) {
                        // Update form data with recalculated values
                        const calculated = data.data;
                        Object.keys(this.formData).forEach(key => {
                            if (calculated.hasOwnProperty(key) && key !== 'status' && key !== 'catatan') {
                                this.formData[key] = parseFloat(calculated[key]) || 0;
                            }
                        });
                        
                        this.calculateTotals();
                        showNotification('success', 'Gaji berhasil dihitung ulang berdasarkan data kehadiran');
                    } else {
                        showNotification('error', data.message || 'Gagal menghitung ulang gaji');
                    }
                } else {
                    showNotification('error', 'Gagal menghitung ulang gaji');
                }
            } catch (error) {
                console.error('Error recalculating salary:', error);
                showNotification('error', 'Gagal menghitung ulang gaji');
            } finally {
                this.loading = false;
            }
        },
        
        async saveChanges() {
            const gajiId = this.getGajiId();
            if (!gajiId) return;
            
            try {
                this.saving = true;
                
                // Prepare data with calculated totals
                const saveData = {
                    ...this.formData,
                    total_pendapatan: this.calculatedTotals.total_pendapatan,
                    total_potongan: this.calculatedTotals.total_potongan,
                    gaji_bersih: this.calculatedTotals.gaji_bersih
                };
                
                const response = await fetch(`/gaji/${gajiId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': window.CSRF_TOKEN
                    },
                    body: JSON.stringify(saveData)
                });
                
                if (response.ok) {
                    const data = await response.json();
                    if (data.success) {
                        showNotification('success', 'Data gaji berhasil diperbarui');
                        setTimeout(() => {
                            window.location.href = `/gaji/${gajiId}`;
                        }, 1000);
                    } else {
                        showNotification('error', data.message || 'Gagal menyimpan perubahan');
                    }
                } else {
                    showNotification('error', 'Gagal menyimpan perubahan');
                }
            } catch (error) {
                console.error('Error saving changes:', error);
                showNotification('error', 'Gagal menyimpan perubahan');
            } finally {
                this.saving = false;
            }
        },
        
        getGajiId() {
            const path = window.location.pathname;
            const segments = path.split('/');
            return segments[segments.length - 2]; // /gaji/{id}/edit
        },
        
        // Helper methods
        getInitials(name) {
            if (!name) return 'N/A';
            return name.split(' ').map(word => word[0]).join('').toUpperCase().substring(0, 2);
        },
        
        formatCurrency(amount) {
            if (!amount) return 'Rp 0';
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
        }
    }
}
</script>
@endpush