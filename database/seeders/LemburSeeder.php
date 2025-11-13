<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class LemburSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get karyawan yang sering lembur (IT, Finance, Customer Support)
        $karyawanList = DB::table('karyawan')
            ->where('status', 'AKTIF')
            ->whereIn('departemen', ['Information Technology', 'Finance', 'Customer Support'])
            ->get();
        
        // Generate data lembur untuk bulan November 2025
        $startDate = Carbon::create(2025, 11, 1);
        $endDate = Carbon::create(2025, 11, 30);
        
        foreach ($karyawanList as $karyawan) {
            $currentDate = $startDate->copy();
            
            while ($currentDate->lte($endDate)) {
                // Skip weekends dan holidays
                if ($currentDate->isWeekend()) {
                    $currentDate->addDay();
                    continue;
                }
                
                $isHoliday = DB::table('hari_libur')
                    ->where('tanggal', $currentDate->format('Y-m-d'))
                    ->exists();
                
                if ($isHoliday) {
                    $currentDate->addDay();
                    continue;
                }
                
                // Skip tanggal masa depan
                if ($currentDate->gt(Carbon::now())) {
                    $currentDate->addDay();
                    continue;
                }
                
                // 25% chance untuk lembur setiap hari kerja
                if (rand(1, 100) <= 25) {
                    $jamMulai = Carbon::parse($currentDate->format('Y-m-d') . ' 17:00:00');
                    $durasiJam = rand(1, 4) + (rand(0, 1) * 0.5); // 1-4.5 jam
                    $jamSelesai = $jamMulai->copy()->addHours(floor($durasiJam))->addMinutes(($durasiJam - floor($durasiJam)) * 60);
                    
                    // Calculate tarif per jam dan total kompensasi berdasarkan gaji pokok
                    $tarifPerJam = $karyawan->gaji_pokok / 173; // 173 = rata-rata jam kerja per bulan
                    $tarifLembur = $tarifPerJam * 1.5; // 1.5x normal rate
                    $totalKompensasi = $tarifLembur * $durasiJam;
                    
                    $keteranganList = [
                        'Project deadline',
                        'System maintenance', 
                        'Bug fixing urgent',
                        'Client support',
                        'Report preparation',
                        'Database optimization',
                        'Server monitoring',
                        'Emergency response',
                        'Code review',
                        'Testing deployment',
                    ];
                    
                    DB::table('lembur')->insert([
                        'id' => Str::uuid(),
                        'karyawan_id' => $karyawan->id,
                        'tanggal' => $currentDate->format('Y-m-d'),
                        'jam_mulai' => $jamMulai,
                        'jam_selesai' => $jamSelesai,
                        'durasi_jam' => round($durasiJam, 2),
                        'keterangan' => $keteranganList[array_rand($keteranganList)],
                        'status' => rand(0, 9) > 0 ? 'DISETUJUI' : 'PENDING', // 90% disetujui, 10% pending
                        'tarif_per_jam' => round($tarifLembur, 2),
                        'total_kompensasi' => round($totalKompensasi, 2),
                        'disetujui_oleh' => rand(0, 1) ? 'Manager IT' : 'Manager HR',
                        'tanggal_disetujui' => rand(0, 9) > 0 ? $currentDate->addDays(rand(1, 3)) : null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
                
                $currentDate->addDay();
            }
        }
    }
}