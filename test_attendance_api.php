<?php

/**
 * Script untuk test API Attendance dengan perhitungan otomatis
 * Mendemonstrasikan integrasi aturan perusahaan dalam perhitungan kehadiran
 */

// Base URL untuk API
$baseUrl = 'http://127.0.0.1:8000/api';

// Function untuk call API
function callAPI($url, $method = 'GET', $data = null) {
    $curl = curl_init();
    
    curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Accept: application/json'
        ),
    ));
    
    if ($data && ($method === 'POST' || $method === 'PUT')) {
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    
    return [
        'code' => $httpCode,
        'data' => json_decode($response, true)
    ];
}

// Function untuk print response
function printResponse($title, $response) {
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "TEST: {$title}\n";
    echo str_repeat("=", 60) . "\n";
    echo "HTTP Code: {$response['code']}\n";
    echo "Response: " . json_encode($response['data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
}

echo "🚀 TESTING ATTENDANCE API WITH AUTOMATIC CALCULATIONS\n";
echo "📅 " . date('Y-m-d H:i:s') . "\n";

try {
    // 1. Test Company Rules Endpoint
    $response = callAPI($baseUrl . '/absensi/company-rules');
    printResponse('GET Company Rules', $response);
    
    // 2. Test Attendance Statistics (gunakan karyawan_id dari seeder)
    // Ambil data karyawan dari database terlebih dahulu
    echo "\n" . str_repeat("-", 40) . "\n";
    echo "📊 Testing dengan sample karyawan...\n";
    
    // Get karyawan list first
    $karyawanResponse = callAPI($baseUrl . '/karyawan');
    if ($karyawanResponse['code'] === 200 && !empty($karyawanResponse['data']['data'])) {
        $sampleKaryawan = $karyawanResponse['data']['data'][0];
        $karyawanId = $sampleKaryawan['id'];
        
        echo "Sample Karyawan: {$sampleKaryawan['nama']} (ID: {$karyawanId})\n";
        
        // Test attendance stats
        $statsResponse = callAPI($baseUrl . "/absensi/stats/{$karyawanId}");
        printResponse('GET Attendance Statistics', $statsResponse);
        
        // Test bonus eligibility
        $bonusResponse = callAPI($baseUrl . "/absensi/bonus-eligibility?karyawan_id={$karyawanId}");
        printResponse('GET Bonus Eligibility', $bonusResponse);
        
        // Test year-end bonus
        $yearBonusResponse = callAPI($baseUrl . "/absensi/year-end-bonus?karyawan_id={$karyawanId}");
        printResponse('GET Year-End Bonus', $yearBonusResponse);
        
    } else {
        echo "❌ Tidak bisa mendapatkan data karyawan untuk testing\n";
    }
    
    // 3. Test Create Attendance with Automatic Calculation
    echo "\n" . str_repeat("-", 40) . "\n";
    echo "📝 Testing Create Attendance dengan Auto Calculation...\n";
    
    if (isset($karyawanId)) {
        $attendanceData = [
            'karyawan_id' => $karyawanId,
            'tanggal' => date('Y-m-d'),
            'jam_masuk' => '08:30:00', // Terlambat 30 menit dari jam kerja 08:00
            'jam_pulang' => '16:30:00', // Pulang tepat waktu
            'keterangan' => 'Test attendance with auto calculation'
        ];
        
        $createResponse = callAPI($baseUrl . '/absensi', 'POST', $attendanceData);
        printResponse('POST Create Attendance', $createResponse);
        
        // Test dengan scenario terlambat
        echo "\n📝 Testing dengan scenario terlambat...\n";
        
        $lateAttendanceData = [
            'karyawan_id' => $karyawanId,
            'tanggal' => date('Y-m-d', strtotime('+1 day')),
            'jam_masuk' => '09:00:00', // Terlambat 1 jam
            'jam_pulang' => '15:30:00', // Pulang awal 30 menit
            'keterangan' => 'Test late attendance with penalties'
        ];
        
        $lateResponse = callAPI($baseUrl . '/absensi', 'POST', $lateAttendanceData);
        printResponse('POST Late Attendance', $lateResponse);
    }
    
} catch (Exception $e) {
    echo "❌ Error during testing: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "🏁 TESTING COMPLETED\n";
echo "💡 Untuk testing lebih lanjut, pastikan Laravel server berjalan di http://127.0.0.1:8000\n";
echo "📖 Dokumentasi API tersedia di routes/api.php\n";
echo str_repeat("=", 60) . "\n";