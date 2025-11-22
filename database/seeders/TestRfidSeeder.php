<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AvailableRfid;
use Illuminate\Support\Str;

class TestRfidSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing RFID data (use delete instead of truncate due to foreign key constraints)
        AvailableRfid::query()->delete();
        
        // Create test RFID cards
        $cards = [
            [
                'id' => Str::uuid(),
                'card_number' => 'CARD000001',
                'card_type' => 'MIFARE_CLASSIC',
                'status' => 'AVAILABLE',
                'assigned_at' => null,
                'notes' => 'Kartu baru tersedia untuk karyawan',
                'karyawan_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'card_number' => 'CARD000002',
                'card_type' => 'MIFARE_CLASSIC',
                'status' => 'AVAILABLE',
                'assigned_at' => null,
                'notes' => null,
                'karyawan_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'card_number' => 'CARD000003',
                'card_type' => 'MIFARE_CLASSIC',
                'status' => 'AVAILABLE',
                'assigned_at' => null,
                'notes' => null,
                'karyawan_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'card_number' => 'CARD000004',
                'card_type' => 'MIFARE_ULTRALIGHT',
                'status' => 'DAMAGED',
                'assigned_at' => null,
                'notes' => 'Kartu rusak - tidak dapat dibaca',
                'karyawan_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'card_number' => 'CARD000005',
                'card_type' => 'MIFARE_CLASSIC',
                'status' => 'LOST',
                'assigned_at' => null,
                'notes' => 'Dilaporkan hilang oleh karyawan',
                'karyawan_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Generate more cards programmatically
        for ($i = 6; $i <= 25; $i++) {
            $cards[] = [
                'id' => Str::uuid(),
                'card_number' => sprintf('CARD%06d', $i),
                'card_type' => collect(['MIFARE_CLASSIC', 'MIFARE_ULTRALIGHT', 'NTAG213'])->random(),
                'status' => collect(['AVAILABLE', 'AVAILABLE', 'AVAILABLE', 'DAMAGED'])->random(),
                'assigned_at' => null,
                'notes' => null,
                'karyawan_id' => null,
                'created_at' => now()->subDays(rand(1, 30)),
                'updated_at' => now()->subDays(rand(0, 10)),
            ];
        }

        // Insert all cards
        foreach ($cards as $card) {
            AvailableRfid::create($card);
        }

        $this->command->info('Created ' . count($cards) . ' test RFID cards successfully!');
    }
}