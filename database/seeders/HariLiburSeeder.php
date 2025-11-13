<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class HariLiburSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $hariLibur = [
            [
                'id' => Str::uuid(),
                'tanggal' => '2025-01-01',
                'nama' => 'Tahun Baru 2025',
                'deskripsi' => 'Libur nasional tahun baru masehi',
                'is_nasional' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'tanggal' => '2025-01-29',
                'nama' => 'Tahun Baru Imlek',
                'deskripsi' => 'Tahun baru China/Imlek 2576',
                'is_nasional' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'tanggal' => '2025-03-29',
                'nama' => 'Hari Raya Nyepi',
                'deskripsi' => 'Tahun baru Saka 1947',
                'is_nasional' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'tanggal' => '2025-03-30',
                'nama' => 'Idul Fitri 1446 H',
                'deskripsi' => 'Hari raya Idul Fitri hari pertama',
                'is_nasional' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'tanggal' => '2025-03-31',
                'nama' => 'Idul Fitri 1446 H',
                'deskripsi' => 'Hari raya Idul Fitri hari kedua',
                'is_nasional' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'tanggal' => '2025-04-18',
                'nama' => 'Wafat Isa Al Masih',
                'deskripsi' => 'Jumat Agung',
                'is_nasional' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'tanggal' => '2025-05-01',
                'nama' => 'Hari Buruh',
                'deskripsi' => 'Hari Buruh Internasional',
                'is_nasional' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'tanggal' => '2025-05-29',
                'nama' => 'Kenaikan Isa Al Masih',
                'deskripsi' => 'Kenaikan Yesus Kristus',
                'is_nasional' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'tanggal' => '2025-06-07',
                'nama' => 'Idul Adha 1446 H',
                'deskripsi' => 'Hari raya kurban',
                'is_nasional' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'tanggal' => '2025-06-27',
                'nama' => 'Tahun Baru Islam 1447 H',
                'deskripsi' => 'Tahun baru hijriyah',
                'is_nasional' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'tanggal' => '2025-08-17',
                'nama' => 'Hari Kemerdekaan RI',
                'deskripsi' => 'HUT kemerdekaan RI ke-80',
                'is_nasional' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'tanggal' => '2025-09-05',
                'nama' => 'Maulid Nabi Muhammad SAW',
                'deskripsi' => 'Hari kelahiran Nabi Muhammad SAW',
                'is_nasional' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'tanggal' => '2025-12-25',
                'nama' => 'Hari Natal',
                'deskripsi' => 'Hari kelahiran Yesus Kristus',
                'is_nasional' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('hari_libur')->insert($hariLibur);
    }
}