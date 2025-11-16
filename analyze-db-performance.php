<?php

// Database Query Optimization and Analysis Script
// Run with: php artisan tinker < analyze-db-performance.php

echo "=== DATABASE PERFORMANCE ANALYSIS ===\n";

try {
    // 1. CHECK CURRENT INDEXES
    echo "1. Analyzing current indexes...\n";
    
    $tables = ['users', 'karyawan', 'absensi', 'gaji', 'lembur', 'available_rfid'];
    
    foreach ($tables as $table) {
        try {
            $indexes = DB::select("SHOW INDEX FROM `{$table}`");
            echo "   📊 Table: {$table} - " . count($indexes) . " indexes\n";
            
            foreach ($indexes as $index) {
                if ($index->Key_name !== 'PRIMARY') {
                    echo "      └─ {$index->Key_name} ({$index->Column_name})\n";
                }
            }
        } catch (Exception $e) {
            echo "      ❌ Table {$table} not found\n";
        }
    }

    echo "\n2. Testing query performance...\n";
    
    // Test critical queries
    $testQueries = [
        "Users by role" => "SELECT COUNT(*) FROM users WHERE role = 'USER'",
        "Active employees" => "SELECT COUNT(*) FROM karyawan WHERE status = 'AKTIF'",
        "Today attendance" => "SELECT COUNT(*) FROM absensi WHERE DATE(tanggal) = CURDATE()",
        "This month attendance" => "SELECT COUNT(*) FROM absensi WHERE YEAR(tanggal) = YEAR(CURDATE()) AND MONTH(tanggal) = MONTH(CURDATE())",
        "Employee with attendance" => "SELECT k.nama, COUNT(a.id) FROM karyawan k LEFT JOIN absensi a ON k.id = a.karyawan_id GROUP BY k.id LIMIT 5"
    ];

    foreach ($testQueries as $description => $query) {
        try {
            $startTime = microtime(true);
            $result = DB::select($query);
            $duration = (microtime(true) - $startTime) * 1000;
            
            $status = $duration < 50 ? '✅' : ($duration < 200 ? '⚠️' : '❌');
            echo "   {$status} {$description}: " . round($duration, 2) . "ms\n";
            
        } catch (Exception $e) {
            echo "   ❌ {$description}: ERROR - " . $e->getMessage() . "\n";
        }
    }

    echo "\n3. Checking table sizes...\n";
    
    foreach ($tables as $table) {
        try {
            $count = DB::select("SELECT COUNT(*) as total FROM `{$table}`")[0]->total;
            $size = DB::select("SELECT 
                ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'size_mb' 
                FROM information_schema.tables 
                WHERE table_schema = DATABASE() AND table_name = '{$table}'");
            
            $sizeMB = isset($size[0]) ? $size[0]->size_mb : 'N/A';
            echo "   📈 {$table}: {$count} records, {$sizeMB} MB\n";
            
        } catch (Exception $e) {
            echo "   ❌ {$table}: ERROR\n";
        }
    }

    echo "\n4. Slow query analysis...\n";
    
    // Check for slow query log
    try {
        $slowLogStatus = DB::select("SHOW VARIABLES LIKE 'slow_query_log'")[0];
        echo "   Slow query log: " . ($slowLogStatus->Value === 'ON' ? '✅ Enabled' : '❌ Disabled') . "\n";
        
        $slowLogTime = DB::select("SHOW VARIABLES LIKE 'long_query_time'")[0];
        echo "   Slow query threshold: " . $slowLogTime->Value . " seconds\n";
    } catch (Exception $e) {
        echo "   ⚠️  Cannot access slow query settings\n";
    }

    echo "\n5. Index usage recommendations...\n";
    
    // Common query patterns that need indexes
    $recommendations = [
        "LOGIN queries" => "email + role indexes on users table",
        "EMPLOYEE SEARCH" => "nama + departemen + status indexes on karyawan",
        "ATTENDANCE REPORTS" => "karyawan_id + tanggal composite index on absensi",
        "SALARY CALCULATIONS" => "karyawan_id + tahun + bulan composite index on gaji",
        "RFID SCANNING" => "rfid_card_number index on karyawan and available_rfid",
        "DATE RANGE QUERIES" => "tanggal indexes on absensi, lembur tables"
    ];

    foreach ($recommendations as $useCase => $recommendation) {
        echo "   💡 {$useCase}: {$recommendation}\n";
    }

    echo "\n6. Memory usage optimization...\n";
    
    try {
        $memory = round(memory_get_usage(true) / 1024 / 1024, 2);
        $memoryPeak = round(memory_get_peak_usage(true) / 1024 / 1024, 2);
        echo "   Current memory: {$memory}MB\n";
        echo "   Peak memory: {$memoryPeak}MB\n";
        
        // Check MySQL settings
        $mysqlMemory = [
            'innodb_buffer_pool_size' => 'InnoDB buffer pool',
            'key_buffer_size' => 'MyISAM key buffer',
            'query_cache_size' => 'Query cache size'
        ];
        
        foreach ($mysqlMemory as $var => $desc) {
            try {
                $setting = DB::select("SHOW VARIABLES LIKE '{$var}'")[0];
                $value = $setting->Value;
                $valueMB = is_numeric($value) ? round($value / 1024 / 1024, 2) . 'MB' : $value;
                echo "   {$desc}: {$valueMB}\n";
            } catch (Exception $e) {
                echo "   {$desc}: Cannot read\n";
            }
        }
        
    } catch (Exception $e) {
        echo "   ⚠️  Memory analysis failed: " . $e->getMessage() . "\n";
    }

    echo "\n=== ANALYSIS COMPLETED ===\n";
    echo "💡 Tip: Run migration to add optimized indexes:\n";
    echo "   php artisan migrate\n\n";
    
} catch (Exception $e) {
    echo "❌ Analysis failed: " . $e->getMessage() . "\n";
}