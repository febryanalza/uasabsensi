#!/bin/bash

# Test Karyawan Creation Fix
echo "🧪 Testing Karyawan Creation Fix..."
echo "=================================="

cd "d:\TUGAS KULIAH\Semester 5\IOT\Project\uasabsensi"

echo "1. Starting Laravel server..."
php artisan serve --port=8001 &
SERVER_PID=$!
sleep 5

echo "2. Testing API endpoint..."
curl -X POST http://127.0.0.1:8001/karyawan/store \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "nip": "EMP001",
    "nama": "John Doe", 
    "email": "john@company.com",
    "jabatan": "Software Developer",
    "departemen": "IT Development",
    "telepon": "081234567890",
    "alamat": "Jakarta",
    "tanggal_masuk": "2024-01-15",
    "gaji_pokok": 8000000,
    "password": "password123",
    "role": "USER"
  }'

echo -e "\n\n3. Checking database..."
php artisan tinker --execute="
  echo 'Karyawan count: ' . App\\Models\\Karyawan::count() . PHP_EOL;
  echo 'User count: ' . App\\Models\\User::count() . PHP_EOL;
  \$karyawan = App\\Models\\Karyawan::latest()->first();
  if (\$karyawan) {
    echo 'Latest Karyawan: ' . \$karyawan->nama . ' (ID: ' . \$karyawan->id . ')' . PHP_EOL;
  }
"

echo "4. Stopping server..."
kill $SERVER_PID

echo "✅ Test completed!"