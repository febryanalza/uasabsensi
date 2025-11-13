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
        Schema::create('absensi', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('karyawan_id');
            $table->date('tanggal');
            $table->dateTime('jam_masuk')->nullable();
            $table->dateTime('jam_keluar')->nullable();
            $table->enum('status', ['HADIR', 'IZIN', 'SAKIT', 'ALPHA', 'CUTI']);
            $table->text('keterangan')->nullable();
            $table->string('lokasi', 255)->nullable();
            $table->string('foto_masuk', 255)->nullable();
            $table->string('foto_keluar', 255)->nullable();
            $table->string('rfid_masuk', 255)->nullable();
            $table->string('rfid_keluar', 255)->nullable();
            
            // Perhitungan potongan
            $table->integer('menit_terlambat')->default(0);
            $table->integer('menit_pulang_cepat')->default(0);
            
            $table->timestamps();
            
            $table->unique(['karyawan_id', 'tanggal'], 'unique_karyawan_tanggal');
            $table->index('karyawan_id', 'idx_karyawan_id');
            $table->index('tanggal', 'idx_tanggal');
            $table->index('status', 'idx_status');
            
            $table->foreign('karyawan_id', 'fk_absensi_karyawan')
                  ->references('id')
                  ->on('karyawan')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absensi');
    }
};
