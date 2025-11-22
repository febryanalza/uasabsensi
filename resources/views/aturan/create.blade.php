@extends('layouts.dashboard')

@section('title', 'Tambah Aturan Perusahaan')
@section('page-title', 'Tambah Aturan Perusahaan')

@section('breadcrumb')
<li class="inline-flex items-center">
    <a href="{{ route('dashboard') }}" class="text-gray-500 hover:text-gray-700">
        <i class="fas fa-home mr-2"></i>
        Dashboard
    </a>
</li>
<li class="inline-flex items-center">
    <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
    <a href="{{ route('aturan.index') }}" class="text-gray-500 hover:text-gray-700">Aturan Perusahaan</a>
</li>
<li class="inline-flex items-center">
    <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
    <span class="text-gray-700 font-medium">Tambah Aturan</span>
</li>
@endsection

@section('header-actions')
<a href="{{ route('aturan.index') }}" 
   class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg font-medium hover:bg-gray-200 transition-all duration-200">
    <i class="fas fa-arrow-left mr-2"></i>
    Kembali
</a>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Form -->
    <div class="bg-white rounded-xl p-6 card-shadow">
        @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

                    <form action="{{ route('aturan.store') }}" method="POST">
                        @csrf
                        
        <!-- Jam Kerja Section -->
        <div class="border-b border-gray-200 pb-6 mb-6">
            <h3 class="text-lg font-semibold text-blue-600 mb-4">
                <i class="fas fa-clock mr-2"></i>Jam Kerja
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="jam_masuk_kerja" class="block text-sm font-medium text-gray-700 mb-2">
                        Jam Masuk Kerja <span class="text-red-500">*</span>
                    </label>
                    <input type="time" 
                           name="jam_masuk_kerja" 
                           id="jam_masuk_kerja" 
                           class="block w-full px-3 py-2 border @error('jam_masuk_kerja') border-red-300 @else border-gray-300 @enderror rounded-lg input-focus"
                           value="{{ old('jam_masuk_kerja', '08:00') }}" 
                           required>
                    @error('jam_masuk_kerja')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="jam_pulang_kerja" class="block text-sm font-medium text-gray-700 mb-2">
                        Jam Pulang Kerja <span class="text-red-500">*</span>
                    </label>
                    <input type="time" 
                           name="jam_pulang_kerja" 
                           id="jam_pulang_kerja" 
                           class="block w-full px-3 py-2 border @error('jam_pulang_kerja') border-red-300 @else border-gray-300 @enderror rounded-lg input-focus"
                           value="{{ old('jam_pulang_kerja', '17:00') }}" 
                           required>
                    @error('jam_pulang_kerja')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

                        <!-- Aturan Keterlambatan Section -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="text-primary mb-3">
                                    <i class="fas fa-exclamation-triangle me-2"></i>Aturan Keterlambatan
                                </h5>
                            </div>
                            <div class="col-md-4">
                                <label for="toleransi_terlambat" class="form-label">Toleransi Terlambat (menit) <span class="text-danger">*</span></label>
                                <input type="number" 
                                       name="toleransi_terlambat" 
                                       id="toleransi_terlambat" 
                                       class="form-control @error('toleransi_terlambat') is-invalid @enderror"
                                       value="{{ old('toleransi_terlambat', 15) }}" 
                                       min="0" 
                                       required>
                                @error('toleransi_terlambat')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="potongan_per_menit_terlambat" class="form-label">Potongan per Menit Terlambat (Rp) <span class="text-danger">*</span></label>
                                <input type="number" 
                                       name="potongan_per_menit_terlambat" 
                                       id="potongan_per_menit_terlambat" 
                                       class="form-control @error('potongan_per_menit_terlambat') is-invalid @enderror"
                                       value="{{ old('potongan_per_menit_terlambat', 0) }}" 
                                       min="0" 
                                       step="0.01" 
                                       required>
                                @error('potongan_per_menit_terlambat')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="potongan_per_hari_alpha" class="form-label">Potongan per Hari Alpha (Rp) <span class="text-danger">*</span></label>
                                <input type="number" 
                                       name="potongan_per_hari_alpha" 
                                       id="potongan_per_hari_alpha" 
                                       class="form-control @error('potongan_per_hari_alpha') is-invalid @enderror"
                                       value="{{ old('potongan_per_hari_alpha', 0) }}" 
                                       min="0" 
                                       step="0.01" 
                                       required>
                                @error('potongan_per_hari_alpha')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Tarif Lembur Section -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="text-primary mb-3">
                                    <i class="fas fa-business-time me-2"></i>Tarif Lembur
                                </h5>
                            </div>
                            <div class="col-md-6">
                                <label for="tarif_lembur_per_jam" class="form-label">Tarif Lembur per Jam (Rp) <span class="text-danger">*</span></label>
                                <input type="number" 
                                       name="tarif_lembur_per_jam" 
                                       id="tarif_lembur_per_jam" 
                                       class="form-control @error('tarif_lembur_per_jam') is-invalid @enderror"
                                       value="{{ old('tarif_lembur_per_jam', 0) }}" 
                                       min="0" 
                                       step="0.01" 
                                       required>
                                @error('tarif_lembur_per_jam')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="tarif_lembur_libur" class="form-label">Tarif Lembur Hari Libur (Rp) <span class="text-danger">*</span></label>
                                <input type="number" 
                                       name="tarif_lembur_libur" 
                                       id="tarif_lembur_libur" 
                                       class="form-control @error('tarif_lembur_libur') is-invalid @enderror"
                                       value="{{ old('tarif_lembur_libur', 0) }}" 
                                       min="0" 
                                       step="0.01" 
                                       required>
                                @error('tarif_lembur_libur')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Bonus Kehadiran Section -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="text-primary mb-3">
                                    <i class="fas fa-gift me-2"></i>Bonus Kehadiran
                                </h5>
                            </div>
                            <div class="col-md-6">
                                <label for="bonus_kehadiran_penuh" class="form-label">Bonus Kehadiran Penuh (Rp) <span class="text-danger">*</span></label>
                                <input type="number" 
                                       name="bonus_kehadiran_penuh" 
                                       id="bonus_kehadiran_penuh" 
                                       class="form-control @error('bonus_kehadiran_penuh') is-invalid @enderror"
                                       value="{{ old('bonus_kehadiran_penuh', 0) }}" 
                                       min="0" 
                                       step="0.01" 
                                       required>
                                @error('bonus_kehadiran_penuh')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="minimal_hadir_bonus" class="form-label">Minimal Hadir untuk Bonus (hari) <span class="text-danger">*</span></label>
                                <input type="number" 
                                       name="minimal_hadir_bonus" 
                                       id="minimal_hadir_bonus" 
                                       class="form-control @error('minimal_hadir_bonus') is-invalid @enderror"
                                       value="{{ old('minimal_hadir_bonus', 22) }}" 
                                       min="0" 
                                       required>
                                @error('minimal_hadir_bonus')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Konfigurasi Umum Section -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="text-primary mb-3">
                                    <i class="fas fa-cog me-2"></i>Konfigurasi Umum
                                </h5>
                            </div>
                            <div class="col-md-6">
                                <label for="hari_kerja_per_bulan" class="form-label">Hari Kerja per Bulan <span class="text-danger">*</span></label>
                                <input type="number" 
                                       name="hari_kerja_per_bulan" 
                                       id="hari_kerja_per_bulan" 
                                       class="form-control @error('hari_kerja_per_bulan') is-invalid @enderror"
                                       value="{{ old('hari_kerja_per_bulan', 22) }}" 
                                       min="1" 
                                       required>
                                @error('hari_kerja_per_bulan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="is_active" class="form-label">Status Aturan <span class="text-danger">*</span></label>
                                <select name="is_active" 
                                        id="is_active" 
                                        class="form-select @error('is_active') is-invalid @enderror" 
                                        required>
                                    <option value="1" {{ old('is_active', 1) == 1 ? 'selected' : '' }}>Aktif</option>
                                    <option value="0" {{ old('is_active') == 0 ? 'selected' : '' }}>Nonaktif</option>
                                </select>
                                @error('is_active')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Jika diaktifkan, aturan lain akan dinonaktifkan secara otomatis.
                                </div>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Simpan Aturan
                                    </button>
                                    <a href="{{ route('aturan.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-times me-2"></i>Batal
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Format currency inputs
    const currencyInputs = document.querySelectorAll('input[name*="potongan"], input[name*="tarif"], input[name*="bonus"]');
    
    currencyInputs.forEach(input => {
        input.addEventListener('input', function() {
            // Remove any non-numeric characters except decimal point
            this.value = this.value.replace(/[^0-9.]/g, '');
        });
    });

    // Validate time inputs
    const jamMasuk = document.getElementById('jam_masuk_kerja');
    const jamPulang = document.getElementById('jam_pulang_kerja');

    function validateTime() {
        if (jamMasuk.value && jamPulang.value) {
            if (jamMasuk.value >= jamPulang.value) {
                jamPulang.setCustomValidity('Jam pulang harus lebih besar dari jam masuk');
            } else {
                jamPulang.setCustomValidity('');
            }
        }
    }

    jamMasuk.addEventListener('change', validateTime);
    jamPulang.addEventListener('change', validateTime);
});
</script>
@endpush