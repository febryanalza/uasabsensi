@extends('layouts.dashboard')

@section('title', 'Hitung Gaji')
@section('page-title', 'Hitung Gaji Karyawan')

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
    <span class="text-gray-700 font-medium">Hitung Gaji</span>
</li>
@endsection

@section('content')
<div class="space-y-6" x-data="gajiCreateData()">
    <!-- Salary Summary Card -->
    <div class="bg-white rounded-xl p-6 card-shadow">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg flex items-center justify-center mr-4">
                    <i class="fas fa-calculator text-white text-xl"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900">Perhitungan Gaji Karyawan</h2>
                    <p class="text-gray-600">Pilih karyawan dan periode untuk menghitung gaji</p>
                </div>
            </div>
            <div x-show="salarySummary.calculation_ready" 
                 class="bg-green-50 px-4 py-2 rounded-lg">
                <p class="text-sm text-green-700">
                    <i class="fas fa-check-circle mr-1"></i>
                    Siap untuk perhitungan
                </p>
            </div>
        </div>
        
        <!-- Period Selection -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Bulan</label>
                <select x-model="calculation.bulan" @change="loadSalarySummary()"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 input-focus">
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
                <select x-model="calculation.tahun" @change="loadSalarySummary()"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 input-focus">
                    <option value="">Pilih Tahun</option>
                    <option value="2024">2024</option>
                    <option value="2025">2025</option>
                    <option value="2026">2026</option>
                </select>
            </div>
            <div class="flex items-end">
                <button @click="loadKaryawanList()" 
                        :disabled="!calculation.bulan || !calculation.tahun"
                        class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                    <i class="fas fa-search mr-2"></i>
                    Cari Karyawan
                </button>
            </div>
        </div>
        
        <!-- Period Summary -->
        <div x-show="salarySummary.period" class="mt-6 p-4 bg-gray-50 rounded-lg">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm">
                <div>
                    <span class="text-gray-600">Periode:</span>
                    <span class="font-medium ml-2" x-text="salarySummary.period?.period_name"></span>
                </div>
                <div>
                    <span class="text-gray-600">Hari Kerja:</span>
                    <span class="font-medium ml-2" x-text="salarySummary.working_days"></span>
                </div>
                <div>
                    <span class="text-gray-600">Hari Libur:</span>
                    <span class="font-medium ml-2" x-text="salarySummary.holidays_count"></span>
                </div>
                <div>
                    <span class="text-gray-600">Karyawan Aktif:</span>
                    <span class="font-medium ml-2" x-text="salarySummary.active_employees"></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Employee Selection -->
    <div x-show="showKaryawanList" class="bg-white rounded-xl p-6 card-shadow">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Pilih Karyawan</h3>
        
        <!-- Search -->
        <div class="mb-4">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400"></i>
                </div>
                <input type="text" 
                       x-model="searchKaryawan"
                       @input.debounce.300ms="filterKaryawan()"
                       placeholder="Cari nama karyawan atau NIP..."
                       class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg input-focus">
            </div>
        </div>
        
        <!-- Employee List -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 max-h-96 overflow-y-auto">
            <template x-for="karyawan in filteredKaryawanList" :key="karyawan.id">
                <div class="border border-gray-200 rounded-lg p-4 hover:border-blue-300 cursor-pointer transition-colors"
                     :class="selectedKaryawan?.id === karyawan.id ? 'border-blue-500 bg-blue-50' : ''"
                     @click="selectKaryawan(karyawan)">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 h-10 w-10">
                            <div class="h-10 w-10 rounded-full bg-gradient-to-r from-blue-600 to-purple-600 flex items-center justify-center">
                                <span class="text-sm font-medium text-white" x-text="getInitials(karyawan.nama)"></span>
                            </div>
                        </div>
                        <div class="ml-3 flex-1">
                            <p class="text-sm font-medium text-gray-900" x-text="karyawan.nama"></p>
                            <p class="text-sm text-gray-500">
                                <span x-text="karyawan.nip"></span> • <span x-text="karyawan.departemen"></span>
                            </p>
                        </div>
                        <div x-show="selectedKaryawan?.id === karyawan.id">
                            <i class="fas fa-check-circle text-blue-600"></i>
                        </div>
                    </div>
                </div>
            </template>
        </div>
        
        <!-- Calculate Button -->
        <div x-show="selectedKaryawan" class="mt-6 flex justify-end space-x-3">
            <button @click="resetSelection()"
                    class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                Reset
            </button>
            <button @click="calculateSalary()" 
                    :disabled="calculating"
                    class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 transition-colors">
                <span x-show="!calculating" class="flex items-center">
                    <i class="fas fa-calculator mr-2"></i>
                    Hitung Gaji
                </span>
                <span x-show="calculating" class="flex items-center">
                    <div class="w-4 h-4 spinner mr-2"></div>
                    Menghitung...
                </span>
            </button>
        </div>
    </div>

    <!-- Salary Calculation Result -->
    <div x-show="calculationResult" class="bg-white rounded-xl p-6 card-shadow">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900">Hasil Perhitungan Gaji</h3>
            <div class="flex space-x-2">
                <button @click="saveGaji()" 
                        :disabled="saving"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 transition-colors">
                    <span x-show="!saving">Simpan</span>
                    <span x-show="saving">Menyimpan...</span>
                </button>
                <button @click="printSlip()" 
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    <i class="fas fa-print mr-2"></i>
                    Print Slip
                </button>
            </div>
        </div>
        
        <template x-if="calculationResult">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Employee Info -->
                <div class="lg:col-span-1">
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h4 class="font-medium text-gray-900 mb-3">Informasi Karyawan</h4>
                        <div class="space-y-2 text-sm">
                            <div><span class="text-gray-600">Nama:</span> <span class="font-medium" x-text="selectedKaryawan?.nama"></span></div>
                            <div><span class="text-gray-600">NIP:</span> <span x-text="selectedKaryawan?.nip"></span></div>
                            <div><span class="text-gray-600">Departemen:</span> <span x-text="selectedKaryawan?.departemen"></span></div>
                            <div><span class="text-gray-600">Periode:</span> <span x-text="formatPeriode(calculationResult.bulan, calculationResult.tahun)"></span></div>
                        </div>
                    </div>
                </div>
                
                <!-- Salary Components -->
                <div class="lg:col-span-2">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Income -->
                        <div>
                            <h4 class="font-medium text-gray-900 mb-3">Pendapatan</h4>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Gaji Pokok:</span>
                                    <span class="font-medium" x-text="formatCurrency(calculationResult.gaji_pokok)"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Tunjangan Jabatan:</span>
                                    <span x-text="formatCurrency(calculationResult.tunjangan_jabatan)"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Tunjangan Transport:</span>
                                    <span x-text="formatCurrency(calculationResult.tunjangan_transport)"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Tunjangan Makan:</span>
                                    <span x-text="formatCurrency(calculationResult.tunjangan_makan)"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Tunjangan Lembur:</span>
                                    <span x-text="formatCurrency(calculationResult.tunjangan_lembur)"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Bonus Kehadiran:</span>
                                    <span x-text="formatCurrency(calculationResult.bonus_kehadiran)"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Bonus KPI:</span>
                                    <span x-text="formatCurrency(calculationResult.bonus_kpi)"></span>
                                </div>
                                <hr class="my-2">
                                <div class="flex justify-between font-medium text-green-600">
                                    <span>Total Pendapatan:</span>
                                    <span x-text="formatCurrency(calculationResult.total_pendapatan)"></span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Deductions -->
                        <div>
                            <h4 class="font-medium text-gray-900 mb-3">Potongan</h4>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Potongan Terlambat:</span>
                                    <span x-text="formatCurrency(calculationResult.potongan_terlambat)"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Potongan Alpha:</span>
                                    <span x-text="formatCurrency(calculationResult.potongan_alpha)"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">BPJS Kesehatan:</span>
                                    <span x-text="formatCurrency(calculationResult.bpjs_kesehatan)"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">BPJS Ketenagakerjaan:</span>
                                    <span x-text="formatCurrency(calculationResult.bpjs_ketenagakerjaan)"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">PPh21:</span>
                                    <span x-text="formatCurrency(calculationResult.pph21)"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Potongan Lainnya:</span>
                                    <span x-text="formatCurrency(calculationResult.potongan_lainnya)"></span>
                                </div>
                                <hr class="my-2">
                                <div class="flex justify-between font-medium text-red-600">
                                    <span>Total Potongan:</span>
                                    <span x-text="formatCurrency(calculationResult.total_potongan)"></span>
                                </div>
                                <hr class="my-2">
                                <div class="flex justify-between font-bold text-purple-600 text-lg">
                                    <span>Gaji Bersih:</span>
                                    <span x-text="formatCurrency(calculationResult.gaji_bersih)"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Attendance Summary -->
                    <div class="mt-6 bg-gray-50 rounded-lg p-4">
                        <h4 class="font-medium text-gray-900 mb-3">Ringkasan Kehadiran</h4>
                        <div class="grid grid-cols-3 md:grid-cols-6 gap-4 text-sm">
                            <div class="text-center">
                                <div class="font-medium text-green-600" x-text="calculationResult.jumlah_hadir"></div>
                                <div class="text-gray-600">Hadir</div>
                            </div>
                            <div class="text-center">
                                <div class="font-medium text-yellow-600" x-text="calculationResult.jumlah_izin"></div>
                                <div class="text-gray-600">Izin</div>
                            </div>
                            <div class="text-center">
                                <div class="font-medium text-blue-600" x-text="calculationResult.jumlah_sakit"></div>
                                <div class="text-gray-600">Sakit</div>
                            </div>
                            <div class="text-center">
                                <div class="font-medium text-red-600" x-text="calculationResult.jumlah_alpha"></div>
                                <div class="text-gray-600">Alpha</div>
                            </div>
                            <div class="text-center">
                                <div class="font-medium text-orange-600" x-text="calculationResult.jumlah_terlambat"></div>
                                <div class="text-gray-600">Terlambat</div>
                            </div>
                            <div class="text-center">
                                <div class="font-medium text-purple-600" x-text="calculationResult.total_jam_lembur"></div>
                                <div class="text-gray-600">Jam Lembur</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>
@endsection

@push('scripts')
<script>
function gajiCreateData() {
    return {
        calculation: {
            bulan: '',
            tahun: ''
        },
        salarySummary: {},
        karyawanList: [],
        filteredKaryawanList: [],
        searchKaryawan: '',
        showKaryawanList: false,
        selectedKaryawan: null,
        calculating: false,
        saving: false,
        calculationResult: null,
        
        async init() {
            // Set default to current month/year
            const now = new Date();
            this.calculation.bulan = now.getMonth() + 1;
            this.calculation.tahun = now.getFullYear();
            
            await this.loadSalarySummary();
        },
        
        async loadSalarySummary() {
            if (!this.calculation.bulan || !this.calculation.tahun) return;
            
            try {
                const params = new URLSearchParams({
                    bulan: this.calculation.bulan,
                    tahun: this.calculation.tahun
                });
                
                const response = await fetch(`/gaji/api/salary-summary?${params.toString()}`);
                if (response.ok) {
                    const data = await response.json();
                    if (data.success) {
                        this.salarySummary = data.data;
                    }
                }
            } catch (error) {
                console.error('Error loading salary summary:', error);
            }
        },
        
        async loadKaryawanList() {
            try {
                const response = await fetch('/gaji/api/karyawan-list');
                if (response.ok) {
                    const data = await response.json();
                    if (data.success) {
                        this.karyawanList = data.data;
                        this.filteredKaryawanList = [...data.data];
                        this.showKaryawanList = true;
                    } else {
                        showNotification('error', 'Gagal memuat data karyawan');
                    }
                } else {
                    showNotification('error', 'Gagal memuat data karyawan');
                }
            } catch (error) {
                console.error('Error loading karyawan list:', error);
                showNotification('error', 'Gagal memuat data karyawan');
            }
        },
        
        filterKaryawan() {
            if (!this.searchKaryawan) {
                this.filteredKaryawanList = [...this.karyawanList];
                return;
            }
            
            const search = this.searchKaryawan.toLowerCase();
            this.filteredKaryawanList = this.karyawanList.filter(karyawan =>
                karyawan.nama.toLowerCase().includes(search) ||
                karyawan.nip.toLowerCase().includes(search) ||
                karyawan.departemen.toLowerCase().includes(search)
            );
        },
        
        selectKaryawan(karyawan) {
            this.selectedKaryawan = karyawan;
        },
        
        resetSelection() {
            this.selectedKaryawan = null;
            this.calculationResult = null;
            this.searchKaryawan = '';
            this.filteredKaryawanList = [...this.karyawanList];
        },
        
        async calculateSalary() {
            if (!this.selectedKaryawan) return;
            
            try {
                this.calculating = true;
                
                const response = await fetch('/gaji/api/calculate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': window.CSRF_TOKEN
                    },
                    body: JSON.stringify({
                        karyawan_id: this.selectedKaryawan.id,
                        bulan: parseInt(this.calculation.bulan),
                        tahun: parseInt(this.calculation.tahun)
                    })
                });
                
                if (response.ok) {
                    const data = await response.json();
                    if (data.success) {
                        this.calculationResult = data.data;
                        showNotification('success', 'Gaji berhasil dihitung');
                    } else {
                        showNotification('error', data.message || 'Gagal menghitung gaji');
                    }
                } else {
                    showNotification('error', 'Gagal menghitung gaji');
                }
            } catch (error) {
                console.error('Error calculating salary:', error);
                showNotification('error', 'Gagal menghitung gaji');
            } finally {
                this.calculating = false;
            }
        },
        
        async saveGaji() {
            try {
                this.saving = true;
                showNotification('success', 'Gaji berhasil disimpan');
                
                // Redirect to gaji list
                setTimeout(() => {
                    window.location.href = '/gaji';
                }, 1000);
            } catch (error) {
                console.error('Error saving gaji:', error);
                showNotification('error', 'Gagal menyimpan gaji');
            } finally {
                this.saving = false;
            }
        },
        
        printSlip() {
            // Implement print functionality
            window.print();
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
        
        formatPeriode(bulan, tahun) {
            if (!bulan || !tahun) return '-';
            const months = [
                '', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
            ];
            return `${months[bulan]} ${tahun}`;
        }
    }
}
</script>
@endpush