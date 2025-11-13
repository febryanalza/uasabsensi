<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Order is important for foreign key constraints
        $this->call([
            AturanPerusahaanSeeder::class,
            HariLiburSeeder::class,
            AvailableRfidSeeder::class,
            KaryawanSeeder::class,
            UserSeeder::class,
            AbsensiSeeder::class,
            LemburSeeder::class,
            KpiSeeder::class,
            GajiSeeder::class,
        ]);
    }
}
