<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AvailableRfidSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rfidCards = [];
        
        // Generate 50 RFID cards
        for ($i = 1; $i <= 50; $i++) {
            $rfidCards[] = [
                'id' => Str::uuid(),
                'card_number' => sprintf('CARD%06d', $i), // CARD000001, CARD000002, etc
                'card_type' => 'MIFARE_CLASSIC',
                'status' => 'AVAILABLE',
                'assigned_at' => null,
                'notes' => null,
                'karyawan_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        DB::table('available_rfid')->insert($rfidCards);
    }
}