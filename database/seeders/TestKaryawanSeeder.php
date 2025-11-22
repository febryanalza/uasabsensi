<?php

use Illuminate\Database\Seeder;
use App\Models\Karyawan;
use App\Models\User;
use App\Models\AvailableRfid;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class TestKaryawanSeeder extends Seeder
{
    public function run()
    {
        try {
            DB::beginTransaction();
            
            // Create karyawan
            $karyawan = Karyawan::create([
                'nip' => 'TEST001',
                'nama' => 'Test Karyawan',
                'email' => 'test@company.com',
                'jabatan' => 'Developer',
                'departemen' => 'IT',
                'telepon' => '08123456789',
                'alamat' => 'Jakarta',
                'tanggal_masuk' => '2024-01-01',
                'status' => 'AKTIF',
                'rfid_card_number' => 'CARD000022',
                'gaji_pokok' => 5000000,
            ]);

            echo "Karyawan created with ID: " . $karyawan->id . "\n";

            // Create user account
            $user = User::create([
                'karyawan_id' => $karyawan->id,
                'name' => $karyawan->nama,
                'email' => $karyawan->email,
                'password' => Hash::make('password123'),
                'role' => 'USER',
            ]);

            echo "User created with ID: " . $user->id . "\n";

            // Update RFID card
            $rfid = AvailableRfid::where('card_number', 'CARD000022')->first();
            if ($rfid) {
                $rfid->update([
                    'karyawan_id' => $karyawan->id,
                    'status' => 'ASSIGNED',
                    'assigned_at' => now(),
                ]);
                echo "RFID card assigned successfully\n";
            }

            DB::commit();
            echo "SUCCESS: Karyawan created successfully!\n";

        } catch (Exception $e) {
            DB::rollBack();
            echo "ERROR: " . $e->getMessage() . "\n";
        }
    }
}