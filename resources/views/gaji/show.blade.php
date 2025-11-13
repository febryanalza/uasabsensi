@extends('layouts.dashboard')

@section('title', 'Detail Gaji')
@section('page-title', 'Detail Gaji Karyawan')

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
    <span class="text-gray-700 font-medium">Detail Gaji</span>
</li>
@endsection

@section('content')
<div class="space-y-6" x-data="gajiDetailData()">
    <!-- Header Actions -->
    <div class="flex justify-between items-start">
        <div class="flex items-center space-x-3">
            <button onclick="window.history.back()" 
                    class="p-2 border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50 transition-colors">
                <i class="fas fa-arrow-left"></i>
            </button>
            <div class="text-sm text-gray-500">
                <span id="loading-text">Loading...</span>
            </div>
        </div>
        
        <div class="flex space-x-2">
            <button @click="printSlip()" 
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                <i class="fas fa-print mr-2"></i>
                Print Slip
            </button>
            <button @click="editGaji()" 
                    class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition-colors">
                <i class="fas fa-edit mr-2"></i>
                Edit Gaji
            </button>
            <button @click="deleteGaji()" 
                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                <i class="fas fa-trash mr-2"></i>
                Hapus
            </button>
        </div>
    </div>

    <!-- Salary Slip Card -->
    <div class="bg-white rounded-xl card-shadow overflow-hidden" id="salary-slip">
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">SLIP GAJI KARYAWAN</h1>
                    <p class="text-blue-100 mt-1">PT. Nama Perusahaan</p>
                </div>
                <div class="text-right">
                    <div class="bg-white bg-opacity-20 rounded-lg p-3">
                        <div class="text-sm text-blue-100">Periode</div>
                        <div class="text-lg font-bold" x-text="gajiData?.period_display"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Employee Information -->
        <div class="p-6 border-b border-gray-200">
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
                        <span class="text-gray-600">Tanggal Lahir:</span>
                        <span class="font-medium" x-text="formatDate(gajiData?.karyawan?.tanggal_lahir)"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">No. Telepon:</span>
                        <span class="font-medium" x-text="gajiData?.karyawan?.telepon"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Email:</span>
                        <span class="font-medium" x-text="gajiData?.karyawan?.email"></span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Salary Details -->
        <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Income Section -->
                <div class="space-y-4">
                    <div class="flex items-center border-b border-gray-200 pb-3">
                        <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-plus text-green-600"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">PENDAPATAN</h3>
                    </div>
                    
                    <div class="space-y-3">
                        <div class="flex justify-between items-center py-2">
                            <span class="text-gray-700">Gaji Pokok</span>
                            <span class="font-medium" x-text="formatCurrency(gajiData?.gaji_pokok)"></span>
                        </div>
                        <div class="flex justify-between items-center py-2">
                            <span class="text-gray-700">Tunjangan Jabatan</span>
                            <span class="font-medium" x-text="formatCurrency(gajiData?.tunjangan_jabatan)"></span>
                        </div>
                        <div class="flex justify-between items-center py-2">
                            <span class="text-gray-700">Tunjangan Transport</span>
                            <span class="font-medium" x-text="formatCurrency(gajiData?.tunjangan_transport)"></span>
                        </div>
                        <div class="flex justify-between items-center py-2">
                            <span class="text-gray-700">Tunjangan Makan</span>
                            <span class="font-medium" x-text="formatCurrency(gajiData?.tunjangan_makan)"></span>
                        </div>
                        <div class="flex justify-between items-center py-2">
                            <span class="text-gray-700">Tunjangan Lembur</span>
                            <span class="font-medium" x-text="formatCurrency(gajiData?.tunjangan_lembur)"></span>
                        </div>
                        <div class="flex justify-between items-center py-2">
                            <span class="text-gray-700">Bonus Kehadiran</span>
                            <span class="font-medium" x-text="formatCurrency(gajiData?.bonus_kehadiran)"></span>
                        </div>
                        <div class="flex justify-between items-center py-2">
                            <span class="text-gray-700">Bonus KPI</span>
                            <span class="font-medium" x-text="formatCurrency(gajiData?.bonus_kpi)"></span>
                        </div>
                        
                        <hr class="my-3">
                        <div class="flex justify-between items-center py-3 bg-green-50 px-4 rounded-lg">
                            <span class="text-lg font-semibold text-green-700">Total Pendapatan</span>
                            <span class="text-lg font-bold text-green-700" x-text="formatCurrency(gajiData?.total_pendapatan)"></span>
                        </div>
                    </div>
                </div>
                
                <!-- Deductions Section -->
                <div class="space-y-4">
                    <div class="flex items-center border-b border-gray-200 pb-3">
                        <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-minus text-red-600"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">POTONGAN</h3>
                    </div>
                    
                    <div class="space-y-3">
                        <div class="flex justify-between items-center py-2">
                            <span class="text-gray-700">Potongan Terlambat</span>
                            <span class="font-medium" x-text="formatCurrency(gajiData?.potongan_terlambat)"></span>
                        </div>
                        <div class="flex justify-between items-center py-2">
                            <span class="text-gray-700">Potongan Alpha</span>
                            <span class="font-medium" x-text="formatCurrency(gajiData?.potongan_alpha)"></span>
                        </div>
                        <div class="flex justify-between items-center py-2">
                            <span class="text-gray-700">BPJS Kesehatan (1%)</span>
                            <span class="font-medium" x-text="formatCurrency(gajiData?.bpjs_kesehatan)"></span>
                        </div>
                        <div class="flex justify-between items-center py-2">
                            <span class="text-gray-700">BPJS Ketenagakerjaan (2%)</span>
                            <span class="font-medium" x-text="formatCurrency(gajiData?.bpjs_ketenagakerjaan)"></span>
                        </div>
                        <div class="flex justify-between items-center py-2">
                            <span class="text-gray-700">PPh21</span>
                            <span class="font-medium" x-text="formatCurrency(gajiData?.pph21)"></span>
                        </div>
                        <div class="flex justify-between items-center py-2">
                            <span class="text-gray-700">Potongan Lainnya</span>
                            <span class="font-medium" x-text="formatCurrency(gajiData?.potongan_lainnya)"></span>
                        </div>
                        
                        <hr class="my-3">
                        <div class="flex justify-between items-center py-3 bg-red-50 px-4 rounded-lg">
                            <span class="text-lg font-semibold text-red-700">Total Potongan</span>
                            <span class="text-lg font-bold text-red-700" x-text="formatCurrency(gajiData?.total_potongan)"></span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Net Salary -->
            <div class="mt-8 p-6 bg-gradient-to-r from-purple-600 to-blue-600 rounded-xl text-white">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="text-xl font-bold">GAJI BERSIH</h3>
                        <p class="text-purple-100 text-sm">Total Pendapatan - Total Potongan</p>
                    </div>
                    <div class="text-right">
                        <div class="text-3xl font-bold" x-text="formatCurrency(gajiData?.gaji_bersih)"></div>
                        <div class="text-purple-100 text-sm" x-text="formatTerbilang(gajiData?.gaji_bersih)"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Attendance Summary -->
        <div class="p-6 bg-gray-50 border-t border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Ringkasan Kehadiran</h3>
            <div class="grid grid-cols-2 md:grid-cols-6 gap-4">
                <div class="bg-white rounded-lg p-4 text-center border-l-4 border-green-500">
                    <div class="text-2xl font-bold text-green-600" x-text="gajiData?.jumlah_hadir || 0"></div>
                    <div class="text-sm text-gray-600">Hadir</div>
                </div>
                <div class="bg-white rounded-lg p-4 text-center border-l-4 border-yellow-500">
                    <div class="text-2xl font-bold text-yellow-600" x-text="gajiData?.jumlah_izin || 0"></div>
                    <div class="text-sm text-gray-600">Izin</div>
                </div>
                <div class="bg-white rounded-lg p-4 text-center border-l-4 border-blue-500">
                    <div class="text-2xl font-bold text-blue-600" x-text="gajiData?.jumlah_sakit || 0"></div>
                    <div class="text-sm text-gray-600">Sakit</div>
                </div>
                <div class="bg-white rounded-lg p-4 text-center border-l-4 border-red-500">
                    <div class="text-2xl font-bold text-red-600" x-text="gajiData?.jumlah_alpha || 0"></div>
                    <div class="text-sm text-gray-600">Alpha</div>
                </div>
                <div class="bg-white rounded-lg p-4 text-center border-l-4 border-orange-500">
                    <div class="text-2xl font-bold text-orange-600" x-text="gajiData?.jumlah_terlambat || 0"></div>
                    <div class="text-sm text-gray-600">Terlambat</div>
                </div>
                <div class="bg-white rounded-lg p-4 text-center border-l-4 border-purple-500">
                    <div class="text-2xl font-bold text-purple-600" x-text="gajiData?.total_jam_lembur || 0"></div>
                    <div class="text-sm text-gray-600">Jam Lembur</div>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="p-6 bg-gray-100 border-t border-gray-200">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-sm text-gray-600">
                <div>
                    <p class="font-medium mb-2">Dicetak pada:</p>
                    <p x-text="formatDate(new Date())"></p>
                </div>
                <div class="text-center">
                    <p class="font-medium mb-2">Disetujui oleh:</p>
                    <div class="mt-8 border-t border-gray-400 pt-1">
                        <p>HRD Manager</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="font-medium mb-2">Diterima oleh:</p>
                    <div class="mt-8 border-t border-gray-400 pt-1">
                        <p>Karyawan</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div x-show="showDeleteModal" 
         x-cloak
         class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50"
         @click="showDeleteModal = false">
        <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4" @click.stop>
            <div class="flex items-center mb-4">
                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                    <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                </div>
            </div>
            <div class="text-center">
                <h3 class="text-lg font-medium text-gray-900 mb-2">
                    Hapus Data Gaji
                </h3>
                <p class="text-sm text-gray-500 mb-4">
                    Apakah Anda yakin ingin menghapus data gaji ini? Tindakan ini tidak dapat dibatalkan.
                </p>
            </div>
            <div class="flex space-x-3">
                <button @click="showDeleteModal = false" 
                        class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Batal
                </button>
                <button @click="confirmDelete()" 
                        :disabled="deleting"
                        class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50 transition-colors">
                    <span x-show="!deleting">Hapus</span>
                    <span x-show="deleting">Menghapus...</span>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function gajiDetailData() {
    return {
        gajiData: null,
        loading: true,
        showDeleteModal: false,
        deleting: false,
        
        async init() {
            await this.loadGajiDetail();
        },
        
        async loadGajiDetail() {
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
                        document.title = `Detail Gaji - ${data.data.karyawan.nama}`;
                        document.getElementById('loading-text').textContent = 
                            `Detail gaji ${data.data.karyawan.nama} - ${data.data.period_display}`;
                    } else {
                        showNotification('error', data.message || 'Gagal memuat detail gaji');
                        window.history.back();
                    }
                } else {
                    showNotification('error', 'Gagal memuat detail gaji');
                    window.history.back();
                }
            } catch (error) {
                console.error('Error loading gaji detail:', error);
                showNotification('error', 'Gagal memuat detail gaji');
                window.history.back();
            } finally {
                this.loading = false;
            }
        },
        
        editGaji() {
            const gajiId = this.getGajiId();
            if (gajiId) {
                window.location.href = `/gaji/${gajiId}/edit`;
            }
        },
        
        deleteGaji() {
            this.showDeleteModal = true;
        },
        
        async confirmDelete() {
            const gajiId = this.getGajiId();
            if (!gajiId) return;
            
            try {
                this.deleting = true;
                
                const response = await fetch(`/gaji/${gajiId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': window.CSRF_TOKEN
                    }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    if (data.success) {
                        showNotification('success', 'Data gaji berhasil dihapus');
                        setTimeout(() => {
                            window.location.href = '/gaji';
                        }, 1000);
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
                this.showDeleteModal = false;
            }
        },
        
        printSlip() {
            // Hide action buttons during print
            const actionButtons = document.querySelector('.flex.justify-between.items-start');
            if (actionButtons) actionButtons.style.display = 'none';
            
            // Print
            window.print();
            
            // Restore action buttons after print
            setTimeout(() => {
                if (actionButtons) actionButtons.style.display = 'flex';
            }, 100);
        },
        
        getGajiId() {
            const path = window.location.pathname;
            const segments = path.split('/');
            return segments[segments.length - 1];
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
        
        formatDate(date) {
            if (!date) return '-';
            const d = new Date(date);
            return d.toLocaleDateString('id-ID', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        },
        
        formatTerbilang(amount) {
            if (!amount) return '';
            // Simple implementation - you can enhance this
            return `(${this.formatCurrency(amount)} rupiah)`;
        }
    }
}
</script>

<style>
@media print {
    @page {
        margin: 1cm;
        size: A4;
    }
    
    body {
        print-color-adjust: exact;
        -webkit-print-color-adjust: exact;
    }
    
    .no-print {
        display: none !important;
    }
    
    .card-shadow {
        box-shadow: none !important;
    }
    
    .bg-gradient-to-r {
        background: linear-gradient(to right, #2563eb, #7c3aed) !important;
    }
}
</style>
@endpush