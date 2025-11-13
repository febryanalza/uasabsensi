<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AturanPerusahaanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('aturan_perusahaan')->insert([
            'id' => Str::uuid(),
            
            // Jam kerja standar
            'jam_masuk_kerja' => '08:00',
            'jam_pulang_kerja' => '17:00',
            
            // Aturan keterlambatan
            'toleransi_terlambat' => 15, // toleransi 15 menit
            'potongan_per_menit_terlambat' => 5000.00, // Rp 5000 per menit
            'potongan_per_hari_alpha' => 150000.00, // Rp 150k per hari alpha
            
            // Tarif lembur
            'tarif_lembur_per_jam' => 50000.00, // Rp 50k per jam
            'tarif_lembur_libur' => 75000.00, // Rp 75k per jam di hari libur
            
            // Bonus kehadiran
            'bonus_kehadiran_penuh' => 500000.00, // Rp 500k bonus kehadiran penuh
            'minimal_hadir_bonus' => 22, // minimal 22 hari hadir untuk bonus
            
            // Konfigurasi umum
            'hari_kerja_per_bulan' => 22,
            'is_active' => true,
            
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}