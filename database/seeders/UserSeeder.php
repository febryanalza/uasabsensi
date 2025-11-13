<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all karyawan data
        $karyawanList = DB::table('karyawan')->get();
        
        foreach ($karyawanList as $karyawan) {
            // Determine role based on jabatan
            $role = 'USER'; // Default role
            
            if (str_contains(strtolower($karyawan->jabatan), 'direktur')) {
                $role = 'ADMIN';
            } elseif (str_contains(strtolower($karyawan->jabatan), 'manager')) {
                $role = 'MANAGER';
            }
            
            DB::table('users')->insert([
                'id' => Str::uuid(),
                'email' => $karyawan->email,
                'name' => $karyawan->nama,
                'role' => $role,
                'karyawan_id' => $karyawan->id,
                'password' => Hash::make('password123'), // Default password
                'remember_token' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}