@extends('layouts.dashboard')

@section('title', 'Detail Aturan Perusahaan')
@section('page-title', 'Detail Aturan Perusahaan')

@section('header-actions')
<div class="flex space-x-2">
    <a href="{{ route('aturan.edit', $aturan->id) }}" 
       class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors duration-200">
        <i class="fas fa-edit w-4 h-4 mr-2"></i>
        Edit Aturan
    </a>
    <a href="{{ route('aturan.index') }}" 
       class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition-colors duration-200">
        <i class="fas fa-arrow-left w-4 h-4 mr-2"></i>
        Kembali
    </a>
</div>
@endsection

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Main Content Card -->
    <div class="bg-white rounded-xl p-6 card-shadow">
        <!-- Header -->
        <div class="border-b border-gray-200 pb-6 mb-6">
            <div class="flex items-start justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $aturan->judul }}</h1>
                    <div class="flex items-center space-x-4 mt-2">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            @switch($aturan->kategori)
                                @case('jam_kerja') bg-blue-100 text-blue-800 @break
                                @case('cuti_izin') bg-green-100 text-green-800 @break
                                @case('disiplin') bg-red-100 text-red-800 @break
                                @case('tunjangan') bg-yellow-100 text-yellow-800 @break
                                @case('evaluasi') bg-purple-100 text-purple-800 @break
                                @default bg-gray-100 text-gray-800
                            @endswitch
                        ">
                            @switch($aturan->kategori)
                                @case('jam_kerja') 
                                    <i class="fas fa-clock mr-1"></i>
                                    Jam Kerja 
                                @break
                                @case('cuti_izin') 
                                    <i class="fas fa-calendar-check mr-1"></i>
                                    Cuti & Izin 
                                @break
                                @case('disiplin') 
                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                    Disiplin 
                                @break
                                @case('tunjangan') 
                                    <i class="fas fa-money-bill mr-1"></i>
                                    Tunjangan 
                                @break
                                @case('evaluasi') 
                                    <i class="fas fa-star mr-1"></i>
                                    Evaluasi 
                                @break
                            @endswitch
                        </span>
                        
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            {{ $aturan->status === 'aktif' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}
                        ">
                            <i class="fas {{ $aturan->status === 'aktif' ? 'fa-check-circle' : 'fa-pause-circle' }} mr-1"></i>
                            {{ ucfirst($aturan->status) }}
                        </span>
                        
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                            <i class="fas fa-sort-numeric-up mr-1"></i>
                            Urutan: {{ $aturan->urutan }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="space-y-6">
            <!-- Deskripsi -->
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-3">
                    <i class="fas fa-file-alt text-gray-600 mr-2"></i>
                    Deskripsi Aturan
                </h3>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-gray-700 leading-relaxed whitespace-pre-line">{{ $aturan->deskripsi }}</p>
                </div>
            </div>

            <!-- Periode Berlaku -->
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-3">
                    <i class="fas fa-calendar text-gray-600 mr-2"></i>
                    Periode Berlaku
                </h3>
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-600">Mulai Berlaku</label>
                            <p class="text-lg font-semibold text-gray-900">
                                {{ \Carbon\Carbon::parse($aturan->berlaku_dari)->translatedFormat('l, d F Y') }}
                            </p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-600">Berlaku Sampai</label>
                            <p class="text-lg font-semibold text-gray-900">
                                @if($aturan->berlaku_sampai)
                                    {{ \Carbon\Carbon::parse($aturan->berlaku_sampai)->translatedFormat('l, d F Y') }}
                                @else
                                    <span class="text-green-600">
                                        <i class="fas fa-infinity mr-1"></i>
                                        Berlaku Permanen
                                    </span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informasi Tambahan -->
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-3">
                    <i class="fas fa-info-circle text-gray-600 mr-2"></i>
                    Informasi Tambahan
                </h3>
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <label class="text-gray-600">Dibuat Pada</label>
                            <p class="font-medium text-gray-900">
                                {{ \Carbon\Carbon::parse($aturan->created_at)->translatedFormat('d F Y, H:i') }}
                            </p>
                        </div>
                        <div>
                            <label class="text-gray-600">Terakhir Diubah</label>
                            <p class="font-medium text-gray-900">
                                {{ \Carbon\Carbon::parse($aturan->updated_at)->translatedFormat('d F Y, H:i') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Status Validitas -->
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-3">
                    <i class="fas fa-shield-alt text-gray-600 mr-2"></i>
                    Status Validitas
                </h3>
                <div class="bg-gray-50 rounded-lg p-4">
                    @php
                        $today = \Carbon\Carbon::today();
                        $startDate = \Carbon\Carbon::parse($aturan->berlaku_dari);
                        $endDate = $aturan->berlaku_sampai ? \Carbon\Carbon::parse($aturan->berlaku_sampai) : null;
                        
                        $isActive = $aturan->status === 'aktif';
                        $isInPeriod = $today->greaterThanOrEqualTo($startDate) && ($endDate === null || $today->lessThanOrEqualTo($endDate));
                        $isValid = $isActive && $isInPeriod;
                    @endphp
                    
                    <div class="flex items-center space-x-4">
                        @if($isValid)
                            <div class="flex items-center text-green-600">
                                <i class="fas fa-check-circle text-xl mr-2"></i>
                                <span class="font-semibold">Aturan Berlaku Aktif</span>
                            </div>
                        @else
                            <div class="flex items-center text-red-600">
                                <i class="fas fa-times-circle text-xl mr-2"></i>
                                <span class="font-semibold">Aturan Tidak Berlaku</span>
                            </div>
                        @endif
                    </div>
                    
                    <div class="mt-2 text-sm text-gray-600">
                        @if(!$isActive)
                            <p>• Status aturan: <span class="font-medium text-red-600">Non-aktif</span></p>
                        @endif
                        @if($today->lessThan($startDate))
                            <p>• Aturan belum berlaku (dimulai {{ $startDate->translatedFormat('d F Y') }})</p>
                        @endif
                        @if($endDate && $today->greaterThan($endDate))
                            <p>• Aturan sudah berakhir (berakhir {{ $endDate->translatedFormat('d F Y') }})</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="bg-white rounded-xl p-6 card-shadow">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Aksi</h3>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('aturan.edit', $aturan->id) }}" 
               class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                <i class="fas fa-edit w-4 h-4 mr-2"></i>
                Edit Aturan
            </a>
            
            @if($aturan->status === 'aktif')
                <button onclick="toggleStatus('{{ $aturan->id }}', 'nonaktif')"
                        class="inline-flex items-center px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                    <i class="fas fa-pause w-4 h-4 mr-2"></i>
                    Non-aktifkan
                </button>
            @else
                <button onclick="toggleStatus('{{ $aturan->id }}', 'aktif')"
                        class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                    <i class="fas fa-play w-4 h-4 mr-2"></i>
                    Aktifkan
                </button>
            @endif
            
            <button onclick="confirmDelete('{{ $aturan->id }}', '{{ $aturan->judul }}')"
                    class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                <i class="fas fa-trash w-4 h-4 mr-2"></i>
                Hapus Aturan
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
async function toggleStatus(id, newStatus) {
    if (!confirm(`Apakah Anda yakin ingin ${newStatus === 'aktif' ? 'mengaktifkan' : 'menonaktifkan'} aturan ini?`)) {
        return;
    }
    
    try {
        const response = await fetch(`/aturan/${id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                status: newStatus
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('success', data.message);
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showNotification('error', data.message);
        }
    } catch (error) {
        showNotification('error', 'Terjadi kesalahan saat mengubah status');
    }
}

function confirmDelete(id, name) {
    if (confirm(`Apakah Anda yakin ingin menghapus aturan "${name}"? Tindakan ini tidak dapat dibatalkan.`)) {
        deleteAturan(id);
    }
}

async function deleteAturan(id) {
    try {
        const response = await fetch(`/aturan/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('success', data.message);
            setTimeout(() => {
                window.location.href = '{{ route("aturan.index") }}';
            }, 1000);
        } else {
            showNotification('error', data.message);
        }
    } catch (error) {
        showNotification('error', 'Terjadi kesalahan saat menghapus aturan');
    }
}
</script>
@endpush