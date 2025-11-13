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
        Schema::create('kpi', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('karyawan_id');
            $table->integer('bulan');
            $table->integer('tahun');
            
            // Target dan realisasi kehadiran
            $table->integer('target_kehadiran')->default(0);
            $table->integer('realisasi_kehadiran')->default(0);
            $table->decimal('persen_kehadiran', 5, 2)->default(0.00);
            
            // Target dan realisasi penyelesaian tugas
            $table->integer('target_penyelesaian_tugas')->default(0);
            $table->integer('realisasi_penyelesaian_tugas')->default(0);
            $table->decimal('persen_penyelesaian_tugas', 5, 2)->default(0.00);
            
            // Nilai kinerja
            $table->decimal('nilai_kedisiplinan', 5, 2)->default(0.00);
            $table->decimal('nilai_kualitas_kerja', 5, 2)->default(0.00);
            $table->decimal('nilai_kerjasama', 5, 2)->default(0.00);
            $table->decimal('nilai_inisiatif', 5, 2)->default(0.00);
            
            // Hasil akhir
            $table->decimal('skor_total', 5, 2)->default(0.00);
            $table->enum('kategori', ['SANGAT_BAIK', 'BAIK', 'CUKUP', 'KURANG', 'SANGAT_KURANG'])->default('CUKUP');
            $table->decimal('bonus_kpi', 15, 2)->default(0.00);
            $table->text('catatan')->nullable();
            
            // Metadata penilaian
            $table->string('dinilai_oleh', 255)->nullable();
            $table->dateTime('tanggal_penilaian')->nullable();
            
            $table->timestamps();
            
            $table->unique(['karyawan_id', 'bulan', 'tahun'], 'unique_karyawan_periode');
            $table->index('karyawan_id', 'idx_karyawan_id');
            $table->index(['bulan', 'tahun'], 'idx_periode');
            $table->index('kategori', 'idx_kategori');
            
            $table->foreign('karyawan_id', 'fk_kpi_karyawan')
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
        Schema::dropIfExists('kpi');
    }
};
