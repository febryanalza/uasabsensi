@extends('layouts.dashboard')

@section('title', 'Aturan Perusahaan')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-1 text-dark fw-bold">Aturan Perusahaan</h4>
                            <p class="text-muted mb-0">Kelola aturan dan kebijakan perusahaan</p>
                        </div>
                        <a href="{{ route('aturan.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Tambah Aturan
                        </a>
                    </div>
                </div>
            </div>

            <!-- Success/Error Messages -->
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            <!-- Current Active Rule Alert -->
            @if($activeRule)
            <div class="card border-success mb-4">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="fas fa-check-circle me-2"></i>Aturan Aktif Saat Ini</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Jam Kerja:</strong> {{ $activeRule->jam_masuk_kerja }} - {{ $activeRule->jam_pulang_kerja }}</p>
                            <p><strong>Toleransi Terlambat:</strong> {{ $activeRule->toleransi_terlambat }} menit</p>
                            <p><strong>Hari Kerja/Bulan:</strong> {{ $activeRule->hari_kerja_per_bulan }} hari</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Bonus Kehadiran Penuh:</strong> Rp {{ number_format($activeRule->bonus_kehadiran_penuh, 0, ',', '.') }}</p>
                            <p><strong>Minimal Hadir untuk Bonus:</strong> {{ $activeRule->minimal_hadir_bonus }} hari</p>
                            <p><strong>Tarif Lembur/Jam:</strong> Rp {{ number_format($activeRule->tarif_lembur_per_jam, 0, ',', '.') }}</p>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('aturan.show', $activeRule->id) }}" class="btn btn-info btn-sm me-2">
                            <i class="fas fa-eye me-1"></i>Detail
                        </a>
                        <a href="{{ route('aturan.edit', $activeRule->id) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit me-1"></i>Edit
                        </a>
                    </div>
                </div>
            </div>
            @else
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Belum ada aturan perusahaan yang aktif. Silakan buat aturan baru dan aktifkan.
            </div>
            @endif

            <!-- Rules List -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 text-dark">Semua Aturan Perusahaan</h5>
                </div>
                <div class="card-body">
                    @if($aturan->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Status</th>
                                    <th>Jam Kerja</th>
                                    <th>Toleransi</th>
                                    <th>Bonus Kehadiran</th>
                                    <th>Tarif Lembur</th>
                                    <th>Dibuat</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($aturan as $rule)
                                <tr>
                                    <td>
                                        @if($rule->is_active)
                                            <span class="badge bg-success">
                                                <i class="fas fa-check me-1"></i>Aktif
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">Nonaktif</span>
                                        @endif
                                    </td>
                                    <td>{{ $rule->jam_masuk_kerja }} - {{ $rule->jam_pulang_kerja }}</td>
                                    <td>{{ $rule->toleransi_terlambat }} menit</td>
                                    <td>Rp {{ number_format($rule->bonus_kehadiran_penuh, 0, ',', '.') }}</td>
                                    <td>Rp {{ number_format($rule->tarif_lembur_per_jam, 0, ',', '.') }}</td>
                                    <td>{{ $rule->created_at->format('d M Y H:i') }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('aturan.show', $rule->id) }}" 
                                               class="btn btn-outline-info btn-sm" title="Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('aturan.edit', $rule->id) }}" 
                                               class="btn btn-outline-warning btn-sm" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @if(!$rule->is_active)
                                            <button type="button" 
                                                    class="btn btn-outline-success btn-sm" 
                                                    onclick="toggleActive('{{ $rule->id }}')" 
                                                    title="Aktifkan">
                                                <i class="fas fa-toggle-on"></i>
                                            </button>
                                            @endif
                                            <form action="{{ route('aturan.destroy', $rule->id) }}" 
                                                  method="POST" 
                                                  class="d-inline"
                                                  onsubmit="return confirm('Yakin ingin menghapus aturan ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="btn btn-outline-danger btn-sm" 
                                                        title="Hapus"
                                                        @if($rule->is_active) disabled @endif>
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-4">
                        {{ $aturan->links() }}
                    </div>
                    @else
                    <div class="text-center py-5">
                        <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Belum ada aturan perusahaan yang dibuat.</p>
                        <a href="{{ route('aturan.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Buat Aturan Pertama
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleActive(id) {
    if (confirm('Aktifkan aturan ini? Aturan yang sedang aktif akan dinonaktifkan.')) {
        fetch(`/api/aturan/${id}/toggle`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Gagal mengaktifkan aturan: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat mengaktifkan aturan.');
        });
    }
}
</script>
@endsection