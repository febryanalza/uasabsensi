<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Optimize absensi table queries
        Schema::table('absensi', function (Blueprint $table) {
            // Add index for jam_masuk and jam_keluar for time-based queries
            try {
                $table->index('jam_masuk', 'idx_absensi_jam_masuk');
                $table->index('jam_keluar', 'idx_absensi_jam_keluar');
            } catch (\Exception $e) {
                // Index might already exist
            }
        });
        
        // Add role and active status to users if not exists
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'role')) {
                $table->string('role', 50)->default('USER')->after('password');
            }
            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('role');
            }
            if (!Schema::hasColumn('users', 'karyawan_id')) {
                $table->uuid('karyawan_id')->nullable()->after('id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('absensi', function (Blueprint $table) {
            try {
                $table->dropIndex('idx_absensi_jam_masuk');
                $table->dropIndex('idx_absensi_jam_keluar');
            } catch (\Exception $e) {
                // Ignore if doesn't exist
            }
        });
    }
};
