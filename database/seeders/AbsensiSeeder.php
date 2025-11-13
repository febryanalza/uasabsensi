<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AbsensiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $karyawanList = DB::table('karyawan')->where('status', 'AKTIF')->get();
        
        // Generate data absensi untuk bulan November 2025 (bulan berjalan)
        $startDate = Carbon::create(2025, 11, 1);
        $endDate = Carbon::create(2025, 11, 30);
        
        foreach ($karyawanList as $karyawan) {
            $currentDate = $startDate->copy();
            
            while ($currentDate->lte($endDate)) {
                // Skip weekends
                if ($currentDate->isWeekend()) {
                    $currentDate->addDay();
                    continue;
                }
                
                // Check if holiday
                $isHoliday = DB::table('hari_libur')
                    ->where('tanggal', $currentDate->format('Y-m-d'))
                    ->exists();
                
                if ($isHoliday) {
                    $currentDate->addDay();
                    continue;
                }
                
                // Skip tanggal masa depan (setelah hari ini)
                if ($currentDate->gt(Carbon::now())) {
                    $currentDate->addDay();
                    continue;
                }
                
                // Random attendance pattern (85% hadir, 8% terlambat, 4% izin, 2% sakit, 1% alpha)
                $rand = rand(1, 100);
                
                if ($rand <= 1) {
                    // 1% alpha
                    DB::table('absensi')->insert([
                        'id' => Str::uuid(),
                        'karyawan_id' => $karyawan->id,
                        'tanggal' => $currentDate->format('Y-m-d'),
                        'jam_masuk' => null,
                        'jam_keluar' => null,
                        'status' => 'ALPHA',
                        'keterangan' => 'Tidak hadir tanpa keterangan',
                        'lokasi' => null,
                        'foto_masuk' => null,
                        'foto_keluar' => null,
                        'rfid_masuk' => null,
                        'rfid_keluar' => null,
                        'menit_terlambat' => 0,
                        'menit_pulang_cepat' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } elseif ($rand <= 3) {
                    // 2% sakit
                    DB::table('absensi')->insert([
                        'id' => Str::uuid(),
                        'karyawan_id' => $karyawan->id,
                        'tanggal' => $currentDate->format('Y-m-d'),
                        'jam_masuk' => null,
                        'jam_keluar' => null,
                        'status' => 'SAKIT',
                        'keterangan' => 'Sakit dengan surat dokter',
                        'lokasi' => null,
                        'foto_masuk' => null,
                        'foto_keluar' => null,
                        'rfid_masuk' => null,
                        'rfid_keluar' => null,
                        'menit_terlambat' => 0,
                        'menit_pulang_cepat' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } elseif ($rand <= 7) {
                    // 4% izin
                    DB::table('absensi')->insert([
                        'id' => Str::uuid(),
                        'karyawan_id' => $karyawan->id,
                        'tanggal' => $currentDate->format('Y-m-d'),
                        'jam_masuk' => null,
                        'jam_keluar' => null,
                        'status' => 'IZIN',
                        'keterangan' => 'Izin keperluan keluarga',
                        'lokasi' => null,
                        'foto_masuk' => null,
                        'foto_keluar' => null,
                        'rfid_masuk' => null,
                        'rfid_keluar' => null,
                        'menit_terlambat' => 0,
                        'menit_pulang_cepat' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } elseif ($rand <= 15) {
                    // 8% terlambat
                    $jamMasuk = Carbon::parse($currentDate->format('Y-m-d') . ' 08:00:00')
                        ->addMinutes(rand(16, 120)); // terlambat 16-120 menit
                    $jamKeluar = Carbon::parse($currentDate->format('Y-m-d') . ' 17:00:00')
                        ->addMinutes(rand(-10, 30));
                    
                    $menitTerlambat = $jamMasuk->diffInMinutes(
                        Carbon::parse($currentDate->format('Y-m-d') . ' 08:00:00')
                    );
                    
                    $menitPulangCepat = 0;
                    if ($jamKeluar->lt(Carbon::parse($currentDate->format('Y-m-d') . ' 17:00:00'))) {
                        $menitPulangCepat = Carbon::parse($currentDate->format('Y-m-d') . ' 17:00:00')
                            ->diffInMinutes($jamKeluar);
                    }
                    
                    DB::table('absensi')->insert([
                        'id' => Str::uuid(),
                        'karyawan_id' => $karyawan->id,
                        'tanggal' => $currentDate->format('Y-m-d'),
                        'jam_masuk' => $jamMasuk,
                        'jam_keluar' => $jamKeluar,
                        'status' => 'HADIR',
                        'keterangan' => "Terlambat {$menitTerlambat} menit",
                        'lokasi' => 'Kantor Pusat',
                        'foto_masuk' => 'masuk_' . $karyawan->nip . '_' . $currentDate->format('Ymd') . '.jpg',
                        'foto_keluar' => 'keluar_' . $karyawan->nip . '_' . $currentDate->format('Ymd') . '.jpg',
                        'rfid_masuk' => $karyawan->rfid_card_number,
                        'rfid_keluar' => $karyawan->rfid_card_number,
                        'menit_terlambat' => $menitTerlambat,
                        'menit_pulang_cepat' => $menitPulangCepat,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    // 85% hadir tepat waktu
                    $jamMasuk = Carbon::parse($currentDate->format('Y-m-d') . ' 08:00:00')
                        ->addMinutes(rand(-5, 14)); // 5 menit lebih awal sampai 14 menit terlambat (masih dalam toleransi)
                    $jamKeluar = Carbon::parse($currentDate->format('Y-m-d') . ' 17:00:00')
                        ->addMinutes(rand(-5, 30));
                    
                    $menitTerlambat = 0;
                    if ($jamMasuk->gt(Carbon::parse($currentDate->format('Y-m-d') . ' 08:00:00'))) {
                        $menitTerlambat = $jamMasuk->diffInMinutes(
                            Carbon::parse($currentDate->format('Y-m-d') . ' 08:00:00')
                        );
                    }
                    
                    $menitPulangCepat = 0;
                    if ($jamKeluar->lt(Carbon::parse($currentDate->format('Y-m-d') . ' 17:00:00'))) {
                        $menitPulangCepat = Carbon::parse($currentDate->format('Y-m-d') . ' 17:00:00')
                            ->diffInMinutes($jamKeluar);
                    }
                    
                    DB::table('absensi')->insert([
                        'id' => Str::uuid(),
                        'karyawan_id' => $karyawan->id,
                        'tanggal' => $currentDate->format('Y-m-d'),
                        'jam_masuk' => $jamMasuk,
                        'jam_keluar' => $jamKeluar,
                        'status' => 'HADIR',
                        'keterangan' => 'Hadir tepat waktu',
                        'lokasi' => 'Kantor Pusat',
                        'foto_masuk' => 'masuk_' . $karyawan->nip . '_' . $currentDate->format('Ymd') . '.jpg',
                        'foto_keluar' => 'keluar_' . $karyawan->nip . '_' . $currentDate->format('Ymd') . '.jpg',
                        'rfid_masuk' => $karyawan->rfid_card_number,
                        'rfid_keluar' => $karyawan->rfid_card_number,
                        'menit_terlambat' => $menitTerlambat,
                        'menit_pulang_cepat' => $menitPulangCepat,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
                
                $currentDate->addDay();
            }
        }
    }
}