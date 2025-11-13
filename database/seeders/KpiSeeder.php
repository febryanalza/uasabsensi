<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class KpiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $karyawanList = DB::table('karyawan')->where('status', 'AKTIF')->get();
        
        // Generate KPI untuk bulan Oktober 2025 (bulan sebelumnya, sudah selesai)
        $bulan = 10;
        $tahun = 2025;
        
        foreach ($karyawanList as $karyawan) {
            // Target based on department and position
            $targetKehadiran = 22; // standard working days
            $targetTugas = match($karyawan->departemen) {
                'Information Technology' => rand(8, 15),
                'Finance' => rand(10, 20),
                'Human Resources' => rand(5, 12),
                'Marketing' => rand(6, 15),
                'Customer Support' => rand(15, 25),
                'Legal' => rand(3, 8),
                'Business Development' => rand(5, 10),
                default => rand(5, 15),
            };
            
            // Realisasi (85-100% dari target)
            $realisasiKehadiran = rand(18, 22);
            $realisasiTugas = rand(ceil($targetTugas * 0.85), $targetTugas);
            
            // Persentase
            $persenKehadiran = round(($realisasiKehadiran / $targetKehadiran) * 100, 2);
            $persenTugas = round(($realisasiTugas / $targetTugas) * 100, 2);
            
            // Nilai kinerja (70-100)
            $nilaiKedisiplinan = rand(70, 100);
            $nilaiKualitas = rand(70, 100);
            $nilaiKerjasama = rand(75, 100);
            $nilaiInisiatif = rand(65, 95);
            
            // Skor total (weighted average)
            $skorTotal = round(
                ($persenKehadiran * 0.2) + 
                ($persenTugas * 0.3) + 
                ($nilaiKedisiplinan * 0.15) + 
                ($nilaiKualitas * 0.2) + 
                ($nilaiKerjasama * 0.1) + 
                ($nilaiInisiatif * 0.05), 2
            );
            
            // Kategori berdasarkan skor
            $kategori = match(true) {
                $skorTotal >= 90 => 'SANGAT_BAIK',
                $skorTotal >= 80 => 'BAIK', 
                $skorTotal >= 70 => 'CUKUP',
                $skorTotal >= 60 => 'KURANG',
                default => 'SANGAT_KURANG',
            };
            
            // Bonus KPI berdasarkan kategori dan gaji pokok
            $bonusKpi = match($kategori) {
                'SANGAT_BAIK' => $karyawan->gaji_pokok * 0.20, // 20% bonus
                'BAIK' => $karyawan->gaji_pokok * 0.15, // 15% bonus
                'CUKUP' => $karyawan->gaji_pokok * 0.10, // 10% bonus
                'KURANG' => $karyawan->gaji_pokok * 0.05, // 5% bonus
                'SANGAT_KURANG' => 0,
            };
            
            $catatan = match($kategori) {
                'SANGAT_BAIK' => 'Kinerja sangat memuaskan, melebihi ekspektasi',
                'BAIK' => 'Kinerja baik, memenuhi target dengan baik',
                'CUKUP' => 'Kinerja memadai, perlu sedikit peningkatan',
                'KURANG' => 'Kinerja perlu ditingkatkan pada beberapa aspek',
                'SANGAT_KURANG' => 'Kinerja di bawah standar, perlu perhatian khusus',
            };
            
            DB::table('kpi')->insert([
                'id' => Str::uuid(),
                'karyawan_id' => $karyawan->id,
                'bulan' => $bulan,
                'tahun' => $tahun,
                
                // Target dan realisasi
                'target_kehadiran' => $targetKehadiran,
                'realisasi_kehadiran' => $realisasiKehadiran,
                'persen_kehadiran' => $persenKehadiran,
                'target_penyelesaian_tugas' => $targetTugas,
                'realisasi_penyelesaian_tugas' => $realisasiTugas,
                'persen_penyelesaian_tugas' => $persenTugas,
                
                // Nilai kinerja
                'nilai_kedisiplinan' => $nilaiKedisiplinan,
                'nilai_kualitas_kerja' => $nilaiKualitas,
                'nilai_kerjasama' => $nilaiKerjasama,
                'nilai_inisiatif' => $nilaiInisiatif,
                
                // Hasil akhir
                'skor_total' => $skorTotal,
                'kategori' => $kategori,
                'bonus_kpi' => round($bonusKpi, 2),
                'catatan' => $catatan,
                
                // Metadata
                'dinilai_oleh' => match($karyawan->departemen) {
                    'Information Technology' => 'Ahmad Dahlan (Manager IT)',
                    'Finance' => 'Rina Wulandari (Manager Finance)', 
                    'Human Resources' => 'Siti Nurhaliza (Manager HR)',
                    default => 'Budi Santoso (Direktur Utama)',
                },
                'tanggal_penilaian' => '2025-11-05', // awal bulan November
                
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}