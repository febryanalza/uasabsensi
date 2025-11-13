<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GajiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $karyawanList = DB::table('karyawan')->where('status', 'AKTIF')->get();
        $aturanPerusahaan = DB::table('aturan_perusahaan')->where('is_active', true)->first();
        
        // Generate gaji untuk bulan Oktober 2025 (sudah selesai)
        $bulan = 10;
        $tahun = 2025;
        
        foreach ($karyawanList as $karyawan) {
            // 1. COPY GAJI POKOK DARI KARYAWAN (MASTER DATA)
            $gajiPokok = $karyawan->gaji_pokok;
            $tunjanganJabatan = $karyawan->tunjangan_jabatan;
            $tunjanganTransport = $karyawan->tunjangan_transport;
            $tunjanganMakan = $karyawan->tunjangan_makan;
            
            // 2. HITUNG TUNJANGAN LEMBUR DARI TABEL LEMBUR
            $totalJamLembur = DB::table('lembur')
                ->where('karyawan_id', $karyawan->id)
                ->whereMonth('tanggal', $bulan)
                ->whereYear('tanggal', $tahun)
                ->where('status', 'DISETUJUI')
                ->sum('durasi_jam');
            
            $tunjanganLembur = $totalJamLembur * $aturanPerusahaan->tarif_lembur_per_jam;
            
            // 3. HITUNG BONUS KEHADIRAN
            $jumlahHadir = DB::table('absensi')
                ->where('karyawan_id', $karyawan->id)
                ->whereMonth('tanggal', $bulan)
                ->whereYear('tanggal', $tahun)
                ->where('status', 'HADIR')
                ->count();
            
            $bonusKehadiran = 0;
            if ($jumlahHadir >= $aturanPerusahaan->minimal_hadir_bonus) {
                $bonusKehadiran = $aturanPerusahaan->bonus_kehadiran_penuh;
            }
            
            // 4. GET BONUS KPI
            $kpi = DB::table('kpi')
                ->where('karyawan_id', $karyawan->id)
                ->where('bulan', $bulan)
                ->where('tahun', $tahun)
                ->first();
            
            $bonusKpi = $kpi ? $kpi->bonus_kpi : 0;
            
            // 5. HITUNG POTONGAN
            // Potongan terlambat
            $totalMenitTerlambat = DB::table('absensi')
                ->where('karyawan_id', $karyawan->id)
                ->whereMonth('tanggal', $bulan)
                ->whereYear('tanggal', $tahun)
                ->where('menit_terlambat', '>', $aturanPerusahaan->toleransi_terlambat)
                ->sum('menit_terlambat');
            
            $potonganTerlambat = ($totalMenitTerlambat - ($aturanPerusahaan->toleransi_terlambat * 
                DB::table('absensi')
                    ->where('karyawan_id', $karyawan->id)
                    ->whereMonth('tanggal', $bulan)
                    ->whereYear('tanggal', $tahun)
                    ->where('menit_terlambat', '>', $aturanPerusahaan->toleransi_terlambat)
                    ->count()
            )) * $aturanPerusahaan->potongan_per_menit_terlambat;
            
            $potonganTerlambat = max(0, $potonganTerlambat); // tidak boleh negatif
            
            // Potongan alpha
            $jumlahAlpha = DB::table('absensi')
                ->where('karyawan_id', $karyawan->id)
                ->whereMonth('tanggal', $bulan)
                ->whereYear('tanggal', $tahun)
                ->where('status', 'ALPHA')
                ->count();
            
            $potonganAlpha = $jumlahAlpha * $aturanPerusahaan->potongan_per_hari_alpha;
            
            // 6. POTONGAN WAJIB (BPJS & PPH21)
            $bpjsKesehatan = $gajiPokok * 0.01; // 1% dari gaji pokok
            $bpjsKetenagakerjaan = $gajiPokok * 0.02; // 2% dari gaji pokok
            
            // PPH21 simplified calculation (5% untuk gaji > 4.5 juta)
            $totalPendapatan = $gajiPokok + $tunjanganJabatan + $tunjanganTransport + $tunjanganMakan + $tunjanganLembur + $bonusKehadiran + $bonusKpi;
            $pph21 = $totalPendapatan > 4500000 ? $totalPendapatan * 0.05 : 0;
            
            // 7. HITUNG TOTAL
            $totalPendapatanFinal = $gajiPokok + $tunjanganJabatan + $tunjanganTransport + $tunjanganMakan + $tunjanganLembur + $bonusKehadiran + $bonusKpi;
            $totalPotongan = $potonganTerlambat + $potonganAlpha + $bpjsKesehatan + $bpjsKetenagakerjaan + $pph21;
            $gajiBersih = $totalPendapatanFinal - $totalPotongan;
            
            // 8. STATISTIK KEHADIRAN
            $jumlahIzin = DB::table('absensi')
                ->where('karyawan_id', $karyawan->id)
                ->whereMonth('tanggal', $bulan)
                ->whereYear('tanggal', $tahun)
                ->where('status', 'IZIN')
                ->count();
            
            $jumlahSakit = DB::table('absensi')
                ->where('karyawan_id', $karyawan->id)
                ->whereMonth('tanggal', $bulan)
                ->whereYear('tanggal', $tahun)
                ->where('status', 'SAKIT')
                ->count();
            
            $jumlahTerlambat = DB::table('absensi')
                ->where('karyawan_id', $karyawan->id)
                ->whereMonth('tanggal', $bulan)
                ->whereYear('tanggal', $tahun)
                ->where('menit_terlambat', '>', $aturanPerusahaan->toleransi_terlambat)
                ->count();
            
            DB::table('gaji')->insert([
                'id' => Str::uuid(),
                'karyawan_id' => $karyawan->id,
                'bulan' => $bulan,
                'tahun' => $tahun,
                
                // Komponen pendapatan
                'gaji_pokok' => $gajiPokok,
                'tunjangan_jabatan' => $tunjanganJabatan,
                'tunjangan_transport' => $tunjanganTransport,
                'tunjangan_makan' => $tunjanganMakan,
                'tunjangan_lembur' => $tunjanganLembur,
                'bonus_kehadiran' => $bonusKehadiran,
                'bonus_kpi' => $bonusKpi,
                
                // Komponen potongan
                'potongan_terlambat' => $potonganTerlambat,
                'potongan_alpha' => $potonganAlpha,
                'potongan_lainnya' => 0,
                'keterangan_potongan' => $potonganTerlambat > 0 || $potonganAlpha > 0 ? 
                    'Potongan keterlambatan dan alpha' : null,
                
                // Potongan wajib
                'bpjs_kesehatan' => $bpjsKesehatan,
                'bpjs_ketenagakerjaan' => $bpjsKetenagakerjaan,
                'pph21' => $pph21,
                
                // Total
                'total_pendapatan' => $totalPendapatanFinal,
                'total_potongan' => $totalPotongan,
                'gaji_bersih' => $gajiBersih,
                
                // Statistik kehadiran
                'jumlah_hadir' => $jumlahHadir,
                'jumlah_izin' => $jumlahIzin,
                'jumlah_sakit' => $jumlahSakit,
                'jumlah_alpha' => $jumlahAlpha,
                'jumlah_terlambat' => $jumlahTerlambat,
                'total_jam_lembur' => $totalJamLembur,
                
                // Status dan metadata
                'status' => 'DIBAYAR',
                'tanggal_dibuat' => '2025-11-01',
                'tanggal_dibayar' => '2025-11-05',
                'dibuat_oleh' => 'System Auto Calculate',
                'catatan_admin' => 'Gaji bulan Oktober 2025 - calculated automatically',
                
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}