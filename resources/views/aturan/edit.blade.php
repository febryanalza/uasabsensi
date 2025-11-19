@extends('layouts.dashboard')

@section('title', 'Edit Aturan Perusahaan')
@section('page-title', 'Edit Aturan Perusahaan')

@section('header-actions')
<a href="{{ route('aturan.index') }}" 
   class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition-colors duration-200">
    <i class="fas fa-arrow-left w-4 h-4 mr-2"></i>
    Kembali
</a>
@endsection

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl p-6 card-shadow" x-data="editAturanData()">
        <!-- Error Messages -->
        @if($errors->any())
        <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form @submit.prevent="submitForm()" class="space-y-6">
            @csrf
            @method('PUT')
            
            <!-- Basic Information -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div>
                    <label for="judul" class="block text-sm font-medium text-gray-700 mb-2">
                        Judul Aturan <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="judul" x-model="formData.judul" value="{{ $aturan->judul ?? '' }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           :class="errors.judul ? 'border-red-300 ring-red-500' : ''"
                           placeholder="Contoh: Jam Masuk dan Pulang Kerja">
                    <p x-show="errors.judul" x-text="errors.judul?.[0]" class="mt-1 text-sm text-red-600"></p>
                </div>

                <div>
                    <label for="kategori" class="block text-sm font-medium text-gray-700 mb-2">
                        Kategori <span class="text-red-500">*</span>
                    </label>
                    <select id="kategori" x-model="formData.kategori"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            :class="errors.kategori ? 'border-red-300 ring-red-500' : ''">
                        <option value="">Pilih Kategori</option>
                        <option value="jam_kerja" {{ ($aturan->kategori ?? '') === 'jam_kerja' ? 'selected' : '' }}>Jam Kerja</option>
                        <option value="cuti_izin" {{ ($aturan->kategori ?? '') === 'cuti_izin' ? 'selected' : '' }}>Cuti & Izin</option>
                        <option value="disiplin" {{ ($aturan->kategori ?? '') === 'disiplin' ? 'selected' : '' }}>Disiplin</option>
                        <option value="tunjangan" {{ ($aturan->kategori ?? '') === 'tunjangan' ? 'selected' : '' }}>Tunjangan</option>
                        <option value="evaluasi" {{ ($aturan->kategori ?? '') === 'evaluasi' ? 'selected' : '' }}>Evaluasi</option>
                    </select>
                    <p x-show="errors.kategori" x-text="errors.kategori?.[0]" class="mt-1 text-sm text-red-600"></p>
                </div>
            </div>

            <!-- Description -->
            <div>
                <label for="deskripsi" class="block text-sm font-medium text-gray-700 mb-2">
                    Deskripsi Aturan <span class="text-red-500">*</span>
                </label>
                <textarea id="deskripsi" x-model="formData.deskripsi" rows="5"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                          :class="errors.deskripsi ? 'border-red-300 ring-red-500' : ''"
                          placeholder="Jelaskan detail aturan perusahaan ini...">{{ $aturan->deskripsi ?? '' }}</textarea>
                <p x-show="errors.deskripsi" x-text="errors.deskripsi?.[0]" class="mt-1 text-sm text-red-600"></p>
            </div>

            <!-- Status and Priority -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                        Status <span class="text-red-500">*</span>
                    </label>
                    <select id="status" x-model="formData.status"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            :class="errors.status ? 'border-red-300 ring-red-500' : ''">
                        <option value="aktif" {{ ($aturan->status ?? 'aktif') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="nonaktif" {{ ($aturan->status ?? '') === 'nonaktif' ? 'selected' : '' }}>Non-aktif</option>
                    </select>
                    <p x-show="errors.status" x-text="errors.status?.[0]" class="mt-1 text-sm text-red-600"></p>
                </div>

                <div>
                    <label for="urutan" class="block text-sm font-medium text-gray-700 mb-2">
                        Urutan Prioritas <span class="text-red-500">*</span>
                    </label>
                    <input type="number" id="urutan" x-model="formData.urutan" min="0" value="{{ $aturan->urutan ?? 0 }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           :class="errors.urutan ? 'border-red-300 ring-red-500' : ''"
                           placeholder="0">
                    <p class="text-xs text-gray-500 mt-1">Urutan tampil aturan (0 = prioritas tertinggi)</p>
                    <p x-show="errors.urutan" x-text="errors.urutan?.[0]" class="mt-1 text-sm text-red-600"></p>
                </div>
            </div>

            <!-- Validity Period -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div>
                    <label for="berlaku_dari" class="block text-sm font-medium text-gray-700 mb-2">
                        Berlaku Dari <span class="text-red-500">*</span>
                    </label>
                    <input type="date" id="berlaku_dari" x-model="formData.berlaku_dari" value="{{ $aturan->berlaku_dari ?? '' }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           :class="errors.berlaku_dari ? 'border-red-300 ring-red-500' : ''">
                    <p x-show="errors.berlaku_dari" x-text="errors.berlaku_dari?.[0]" class="mt-1 text-sm text-red-600"></p>
                </div>

                <div>
                    <label for="berlaku_sampai" class="block text-sm font-medium text-gray-700 mb-2">
                        Berlaku Sampai
                    </label>
                    <input type="date" id="berlaku_sampai" x-model="formData.berlaku_sampai" value="{{ $aturan->berlaku_sampai ?? '' }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           :class="errors.berlaku_sampai ? 'border-red-300 ring-red-500' : ''">
                    <p class="text-xs text-gray-500 mt-1">Kosongkan jika berlaku permanen</p>
                    <p x-show="errors.berlaku_sampai" x-text="errors.berlaku_sampai?.[0]" class="mt-1 text-sm text-red-600"></p>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
                <a href="{{ route('aturan.index') }}" 
                   class="inline-flex items-center px-6 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 text-sm font-medium rounded-lg transition-colors duration-200">
                    Batal
                </a>
                <button type="submit" 
                        :disabled="loading"
                        class="inline-flex items-center px-6 py-2 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                    <span x-show="!loading">
                        <i class="fas fa-save w-4 h-4 mr-2"></i>
                        Update Aturan
                    </span>
                    <span x-show="loading" class="flex items-center">
                        <div class="spinner w-4 h-4 mr-2"></div>
                        Mengupdate...
                    </span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function editAturanData() {
    return {
        formData: {
            judul: '',
            deskripsi: '',
            kategori: '',
            status: 'aktif',
            urutan: 0,
            berlaku_dari: '',
            berlaku_sampai: ''
        },
        errors: {},
        loading: false,
        
        init() {
            // Load existing data from form fields
            this.loadExistingData();
        },
        
        loadExistingData() {
            const judulEl = document.getElementById('judul');
            const deskripsiEl = document.getElementById('deskripsi');
            const kategoriEl = document.getElementById('kategori');
            const statusEl = document.getElementById('status');
            const urutanEl = document.getElementById('urutan');
            const berlakuDariEl = document.getElementById('berlaku_dari');
            const berlakuSampaiEl = document.getElementById('berlaku_sampai');
            
            if (judulEl && judulEl.value) this.formData.judul = judulEl.value;
            if (deskripsiEl && deskripsiEl.value) this.formData.deskripsi = deskripsiEl.value;
            if (kategoriEl && kategoriEl.value) this.formData.kategori = kategoriEl.value;
            if (statusEl && statusEl.value) this.formData.status = statusEl.value;
            if (urutanEl && urutanEl.value) this.formData.urutan = parseInt(urutanEl.value);
            if (berlakuDariEl && berlakuDariEl.value) this.formData.berlaku_dari = berlakuDariEl.value;
            if (berlakuSampaiEl && berlakuSampaiEl.value) this.formData.berlaku_sampai = berlakuSampaiEl.value;
        },
        
        async submitForm() {
            try {
                this.loading = true;
                this.errors = {};
                
                const response = await fetch('/aturan/{{ $aturan->id }}', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(this.formData)
                });
                
                const data = await response.json();
                
                if (response.ok && data.success) {
                    showNotification('success', 'Aturan perusahaan berhasil diperbarui');
                    setTimeout(() => {
                        window.location.href = '{{ route("aturan.index") }}';
                    }, 1000);
                } else {
                    if (data.errors) {
                        this.errors = data.errors;
                        showNotification('error', 'Silakan perbaiki kesalahan pada form');
                    } else {
                        showNotification('error', data.message || 'Gagal memperbarui aturan');
                    }
                }
                
            } catch (error) {
                console.error('Error updating aturan:', error);
                showNotification('error', 'Terjadi kesalahan. Silakan coba lagi.');
            } finally {
                this.loading = false;
            }
        }
    }
}
</script>
@endpush