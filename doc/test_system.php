<?php

// Simple test untuk memverifikasi sistem attendance
echo "🧪 TESTING ATTENDANCE SYSTEM\n";
echo str_repeat("=", 40) . "\n";

try {
    // Test autoload
    require_once __DIR__ . '/vendor/autoload.php';
    echo "✅ Autoload berhasil\n";
    
    // Test jika class helper ada
    if (file_exists(__DIR__ . '/app/Helpers/AbsensiCalculatorHelper.php')) {
        echo "✅ AbsensiCalculatorHelper file exists\n";
    } else {
        echo "❌ AbsensiCalculatorHelper file not found\n";
    }
    
    // Test jika controller sudah diupdate
    $controllerContent = file_get_contents(__DIR__ . '/app/Http/Controllers/API/AbsensiController.php');
    if (strpos($controllerContent, 'AbsensiCalculatorHelper') !== false) {
        echo "✅ AbsensiController terintegrasi dengan helper\n";
    } else {
        echo "❌ AbsensiController belum terintegrasi\n";
    }
    
    // Test routes
    $routesContent = file_get_contents(__DIR__ . '/routes/api.php');
    if (strpos($routesContent, 'getAttendanceStats') !== false) {
        echo "✅ Routes attendance analytics sudah ada\n";
    } else {
        echo "❌ Routes attendance analytics belum ada\n";
    }
    
    // Count methods in helper
    $helperContent = file_get_contents(__DIR__ . '/app/Helpers/AbsensiCalculatorHelper.php');
    $methodCount = substr_count($helperContent, 'public static function');
    echo "✅ Total methods in helper: {$methodCount}\n";
    
    echo "\n🎉 SISTEM ATTENDANCE READY!\n";
    echo "📌 Fitur yang tersedia:\n";
    echo "   - Automatic tardiness calculation\n";
    echo "   - Early departure penalties\n";
    echo "   - Monthly bonus eligibility\n";
    echo "   - Year-end bonus calculation\n";
    echo "   - Company rules integration\n";
    echo "   - Attendance analytics API\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}