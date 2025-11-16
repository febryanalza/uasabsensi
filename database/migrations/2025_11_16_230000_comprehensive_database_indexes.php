<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


return new class extends Migration
{
    /**
     * Run the migrations - Comprehensive Database Indexes Optimization
     */
    public function up(): void
    {
        // ==========================================
        // 1. USERS TABLE INDEXES
        // ==========================================
        Schema::table('users', function (Blueprint $table) {
            try {
                // Performance indexes untuk query umum
                $this->addIndexIfNotExists('users', 'email', 'idx_users_email');
                $this->addIndexIfNotExists('users', 'role', 'idx_users_role');
                $this->addIndexIfNotExists('users', 'karyawan_id', 'idx_users_karyawan_id');
                $this->addIndexIfNotExists('users', 'created_at', 'idx_users_created_at');
                $this->addIndexIfNotExists('users', ['role', 'created_at'], 'idx_users_role_created');
                
                // Index untuk login dan authentication
                $this->addIndexIfNotExists('users', ['email', 'role'], 'idx_users_email_role');
            } catch (\Exception $e) {
                \Log::info('Users indexes may already exist: ' . $e->getMessage());
            }
        });

        // ==========================================
        // 2. KARYAWAN TABLE INDEXES  
        // ==========================================
        Schema::table('karyawan', function (Blueprint $table) {
            try {
                // Primary search columns
                $this->addIndexIfNotExists('karyawan', 'nip', 'idx_karyawan_nip');
                $this->addIndexIfNotExists('karyawan', 'email', 'idx_karyawan_email');
                $this->addIndexIfNotExists('karyawan', 'nama', 'idx_karyawan_nama');
                
                // Filtering columns
                $this->addIndexIfNotExists('karyawan', 'departemen', 'idx_karyawan_departemen');
                $this->addIndexIfNotExists('karyawan', 'jabatan', 'idx_karyawan_jabatan');
                $this->addIndexIfNotExists('karyawan', 'status', 'idx_karyawan_status');
                
                // Date columns untuk laporan
                $this->addIndexIfNotExists('karyawan', 'tanggal_masuk', 'idx_karyawan_tanggal_masuk');
                $this->addIndexIfNotExists('karyawan', 'created_at', 'idx_karyawan_created_at');
                
                // RFID operations
                $this->addIndexIfNotExists('karyawan', 'rfid_card_number', 'idx_karyawan_rfid');
                
                // Composite indexes untuk query kompleks
                $this->addIndexIfNotExists('karyawan', ['departemen', 'status'], 'idx_karyawan_dept_status');
                $this->addIndexIfNotExists('karyawan', ['jabatan', 'status'], 'idx_karyawan_jabatan_status');
                $this->addIndexIfNotExists('karyawan', ['status', 'tanggal_masuk'], 'idx_karyawan_status_tanggal');
                
                // Salary calculation indexes
                $this->addIndexIfNotExists('karyawan', 'gaji_pokok', 'idx_karyawan_gaji_pokok');
            } catch (\Exception $e) {
                \Log::info('Karyawan indexes may already exist: ' . $e->getMessage());
            }
        });

        // ==========================================
        // 3. ABSENSI TABLE INDEXES (CRITICAL!)
        // ==========================================
        Schema::table('absensi', function (Blueprint $table) {
            try {
                // Core search indexes
                $this->addIndexIfNotExists('absensi', 'karyawan_id', 'idx_absensi_karyawan_id');
                $this->addIndexIfNotExists('absensi', 'tanggal', 'idx_absensi_tanggal');
                $this->addIndexIfNotExists('absensi', 'status', 'idx_absensi_status');
                
                // Time-based indexes
                $this->addIndexIfNotExists('absensi', 'jam_masuk', 'idx_absensi_jam_masuk');
                $this->addIndexIfNotExists('absensi', 'jam_keluar', 'idx_absensi_jam_keluar');
                $this->addIndexIfNotExists('absensi', 'created_at', 'idx_absensi_created_at');
                
                // RFID operation indexes
                $this->addIndexIfNotExists('absensi', 'rfid_masuk', 'idx_absensi_rfid_masuk');
                $this->addIndexIfNotExists('absensi', 'rfid_keluar', 'idx_absensi_rfid_keluar');
                
                // Performance calculation indexes
                $this->addIndexIfNotExists('absensi', 'menit_terlambat', 'idx_absensi_terlambat');
                $this->addIndexIfNotExists('absensi', 'menit_pulang_cepat', 'idx_absensi_pulang_cepat');
                
                // CRITICAL: Composite indexes untuk laporan
                $this->addIndexIfNotExists('absensi', ['karyawan_id', 'tanggal'], 'idx_absensi_karyawan_tanggal');
                $this->addIndexIfNotExists('absensi', ['tanggal', 'status'], 'idx_absensi_tanggal_status');
                $this->addIndexIfNotExists('absensi', ['karyawan_id', 'status'], 'idx_absensi_karyawan_status');
                
                // Monthly/Yearly reports
                $this->addIndexIfNotExists('absensi', ['karyawan_id', 'tanggal', 'status'], 'idx_absensi_karyawan_tanggal_status');
                
                // Date range queries optimization
                $this->addCompositeIndexForDateRange('absensi', 'tanggal');
                
            } catch (\Exception $e) {
                \Log::info('Absensi indexes may already exist: ' . $e->getMessage());
            }
        });

        // ==========================================
        // 4. GAJI TABLE INDEXES
        // ==========================================
        Schema::table('gaji', function (Blueprint $table) {
            try {
                // Core query indexes
                $this->addIndexIfNotExists('gaji', 'karyawan_id', 'idx_gaji_karyawan_id');
                $this->addIndexIfNotExists('gaji', 'bulan', 'idx_gaji_bulan');
                $this->addIndexIfNotExists('gaji', 'tahun', 'idx_gaji_tahun');
                $this->addIndexIfNotExists('gaji', 'created_at', 'idx_gaji_created_at');
                
                // Calculation fields
                $this->addIndexIfNotExists('gaji', 'gaji_bersih', 'idx_gaji_gaji_bersih');
                $this->addIndexIfNotExists('gaji', 'total_pendapatan', 'idx_gaji_total_pendapatan');
                
                // CRITICAL: Period-based indexes
                $this->addIndexIfNotExists('gaji', ['tahun', 'bulan'], 'idx_gaji_periode');
                $this->addIndexIfNotExists('gaji', ['karyawan_id', 'tahun'], 'idx_gaji_karyawan_tahun');
                $this->addIndexIfNotExists('gaji', ['karyawan_id', 'tahun', 'bulan'], 'idx_gaji_karyawan_periode');
                
                // Attendance statistics
                $this->addIndexIfNotExists('gaji', 'jumlah_hadir', 'idx_gaji_hadir');
                $this->addIndexIfNotExists('gaji', 'jumlah_alpha', 'idx_gaji_alpha');
            } catch (\Exception $e) {
                \Log::info('Gaji indexes may already exist: ' . $e->getMessage());
            }
        });

        // ==========================================
        // 5. LEMBUR TABLE INDEXES
        // ==========================================
        if (Schema::hasTable('lembur')) {
            Schema::table('lembur', function (Blueprint $table) {
                try {
                    $this->addIndexIfNotExists('lembur', 'karyawan_id', 'idx_lembur_karyawan_id');
                    $this->addIndexIfNotExists('lembur', 'tanggal', 'idx_lembur_tanggal');
                    $this->addIndexIfNotExists('lembur', 'status', 'idx_lembur_status');
                    $this->addIndexIfNotExists('lembur', ['karyawan_id', 'tanggal'], 'idx_lembur_karyawan_tanggal');
                    $this->addIndexIfNotExists('lembur', 'jam_mulai', 'idx_lembur_jam_mulai');
                    $this->addIndexIfNotExists('lembur', 'jam_selesai', 'idx_lembur_jam_selesai');
                } catch (\Exception $e) {
                    \Log::info('Lembur indexes may already exist: ' . $e->getMessage());
                }
            });
        }

        // ==========================================
        // 6. AVAILABLE_RFID TABLE INDEXES
        // ==========================================
        Schema::table('available_rfid', function (Blueprint $table) {
            try {
                $this->addIndexIfNotExists('available_rfid', 'card_number', 'idx_rfid_card_number');
                $this->addIndexIfNotExists('available_rfid', 'is_assigned', 'idx_rfid_is_assigned');
                $this->addIndexIfNotExists('available_rfid', 'created_at', 'idx_rfid_created_at');
                $this->addIndexIfNotExists('available_rfid', ['is_assigned', 'created_at'], 'idx_rfid_assigned_created');
            } catch (\Exception $e) {
                Log::info('RFID indexes may already exist: ' . $e->getMessage());
            }
        });

        // ==========================================
        // 7. SESSIONS TABLE INDEXES (Performance critical)
        // ==========================================
        if (Schema::hasTable('sessions')) {
            Schema::table('sessions', function (Blueprint $table) {
                try {
                    $this->addIndexIfNotExists('sessions', 'user_id', 'idx_sessions_user_id');
                    $this->addIndexIfNotExists('sessions', 'last_activity', 'idx_sessions_last_activity');
                    $this->addIndexIfNotExists('sessions', ['user_id', 'last_activity'], 'idx_sessions_user_activity');
                } catch (\Exception $e) {
                    Log::info('Sessions indexes may already exist: ' . $e->getMessage());
                }
            });
        }

        // ==========================================
        // 8. CACHE TABLE INDEXES
        // ==========================================
        if (Schema::hasTable('cache')) {
            Schema::table('cache', function (Blueprint $table) {
                try {
                    $this->addIndexIfNotExists('cache', 'key', 'idx_cache_key');
                    $this->addIndexIfNotExists('cache', 'expiration', 'idx_cache_expiration');
                } catch (\Exception $e) {
                    Log::info('Cache indexes may already exist: ' . $e->getMessage());
                }
            });
        }

        // ==========================================
        // 9. KPI TABLE INDEXES (if exists)
        // ==========================================
        if (Schema::hasTable('kpi')) {
            Schema::table('kpi', function (Blueprint $table) {
                try {
                    $this->addIndexIfNotExists('kpi', 'karyawan_id', 'idx_kpi_karyawan_id');
                    $this->addIndexIfNotExists('kpi', 'bulan', 'idx_kpi_bulan');
                    $this->addIndexIfNotExists('kpi', 'tahun', 'idx_kpi_tahun');
                    $this->addIndexIfNotExists('kpi', ['karyawan_id', 'tahun', 'bulan'], 'idx_kpi_karyawan_periode');
                } catch (\Exception $e) {
                    Log::info('KPI indexes may already exist: ' . $e->getMessage());
                }
            });
        }

        // Log successful completion
        Log::info('Database performance indexes optimization completed successfully');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop all custom indexes
        try {
            $indexes = [
                'users' => ['idx_users_email', 'idx_users_role', 'idx_users_karyawan_id', 'idx_users_created_at', 'idx_users_role_created', 'idx_users_email_role'],
                'karyawan' => ['idx_karyawan_nip', 'idx_karyawan_email', 'idx_karyawan_nama', 'idx_karyawan_departemen', 'idx_karyawan_jabatan', 'idx_karyawan_status', 'idx_karyawan_tanggal_masuk', 'idx_karyawan_created_at', 'idx_karyawan_rfid', 'idx_karyawan_dept_status', 'idx_karyawan_jabatan_status', 'idx_karyawan_status_tanggal', 'idx_karyawan_gaji_pokok'],
                'absensi' => ['idx_absensi_karyawan_id', 'idx_absensi_tanggal', 'idx_absensi_status', 'idx_absensi_jam_masuk', 'idx_absensi_jam_keluar', 'idx_absensi_created_at', 'idx_absensi_rfid_masuk', 'idx_absensi_rfid_keluar', 'idx_absensi_terlambat', 'idx_absensi_pulang_cepat', 'idx_absensi_karyawan_tanggal', 'idx_absensi_tanggal_status', 'idx_absensi_karyawan_status', 'idx_absensi_karyawan_tanggal_status'],
                'gaji' => ['idx_gaji_karyawan_id', 'idx_gaji_bulan', 'idx_gaji_tahun', 'idx_gaji_created_at', 'idx_gaji_gaji_bersih', 'idx_gaji_total_pendapatan', 'idx_gaji_periode', 'idx_gaji_karyawan_tahun', 'idx_gaji_karyawan_periode', 'idx_gaji_hadir', 'idx_gaji_alpha'],
            ];

            foreach ($indexes as $table => $tableIndexes) {
                foreach ($tableIndexes as $index) {
                    $this->dropIndexIfExists($table, $index);
                }
            }
        } catch (\Exception $e) {
            Log::info('Index cleanup completed with some warnings: ' . $e->getMessage());
        }
    }

    /**
     * Helper function to add index if it doesn't exist
     */
    private function addIndexIfNotExists(string $table, $columns, string $indexName): void
    {
        try {
            $indexExists = false;
            
            // Check if index exists
            $indexes = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);
            if (count($indexes) > 0) {
                $indexExists = true;
            }

            if (!$indexExists) {
                Schema::table($table, function (Blueprint $blueprint) use ($columns, $indexName) {
                    $blueprint->index($columns, $indexName);
                });
                Log::info("Added index {$indexName} to table {$table}");
            } else {
                Log::info("Index {$indexName} already exists on table {$table}");
            }
        } catch (\Exception $e) {
            Log::warning("Could not add index {$indexName} to table {$table}: " . $e->getMessage());
        }
    }

    /**
     * Helper function to drop index if it exists
     */
    private function dropIndexIfExists(string $table, string $indexName): void
    {
        try {
            $indexes = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);
            if (count($indexes) > 0) {
                Schema::table($table, function (Blueprint $blueprint) use ($indexName) {
                    $blueprint->dropIndex($indexName);
                });
                Log::info("Dropped index {$indexName} from table {$table}");
            }
        } catch (\Exception $e) {
            Log::warning("Could not drop index {$indexName} from table {$table}: " . $e->getMessage());
        }
    }

    /**
     * Add specialized composite indexes for date range queries
     */
    private function addCompositeIndexForDateRange(string $table, string $dateColumn): void
    {
        try {
            // Add covering indexes for common date range patterns
            $yearMonth = "YEAR({$dateColumn}), MONTH({$dateColumn})";
            $indexName = "idx_{$table}_year_month";
            
            DB::statement("CREATE INDEX {$indexName} ON {$table} (YEAR({$dateColumn}), MONTH({$dateColumn}))");
            Log::info("Added date range index {$indexName} to table {$table}");
        } catch (\Exception $e) {
            Log::info("Date range index may already exist: " . $e->getMessage());
        }
    }
};