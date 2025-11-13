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
        Schema::create('lembur', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('karyawan_id');
            $table->date('tanggal');
            $table->dateTime('jam_mulai');
            $table->dateTime('jam_selesai');
            $table->decimal('durasi_jam', 5, 2);
            $table->text('keterangan')->nullable();
            $table->enum('status', ['PENDING', 'DISETUJUI', 'DITOLAK'])->default('PENDING');
            $table->decimal('tarif_per_jam', 15, 2);
            $table->decimal('total_kompensasi', 15, 2);
            $table->string('disetujui_oleh', 255)->nullable();
            $table->dateTime('tanggal_disetujui')->nullable();
            $table->timestamps();
            
            $table->index('karyawan_id', 'idx_karyawan_id');
            $table->index('tanggal', 'idx_tanggal');
            $table->index('status', 'idx_status');
            
            $table->foreign('karyawan_id', 'fk_lembur_karyawan')
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
        Schema::dropIfExists('lembur');
    }
};
