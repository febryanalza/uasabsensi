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
        Schema::create('aturan_perusahaan', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // Jam kerja
            $table->string('jam_masuk_kerja', 10)->default('08:00');
            $table->string('jam_pulang_kerja', 10)->default('17:00');
            
            // Aturan keterlambatan
            $table->integer('toleransi_terlambat')->default(15);
            $table->decimal('potongan_per_menit_terlambat', 15, 2)->default(0.00);
            $table->decimal('potongan_per_hari_alpha', 15, 2)->default(0.00);
            
            // Tarif lembur
            $table->decimal('tarif_lembur_per_jam', 15, 2)->default(0.00);
            $table->decimal('tarif_lembur_libur', 15, 2)->default(0.00);
            
            // Bonus kehadiran
            $table->decimal('bonus_kehadiran_penuh', 15, 2)->default(0.00);
            $table->integer('minimal_hadir_bonus')->default(22);
            
            // Konfigurasi umum
            $table->integer('hari_kerja_per_bulan')->default(22);
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aturan_perusahaan');
    }
};
