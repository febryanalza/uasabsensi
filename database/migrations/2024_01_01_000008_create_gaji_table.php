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
        Schema::create('gaji', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('karyawan_id');
            $table->integer('bulan');
            $table->integer('tahun');
            
            // Komponen pendapatan (diambil dari karyawan + perhitungan)
            $table->decimal('gaji_pokok', 15, 2)->comment('Copy dari karyawan.gaji_pokok');
            $table->decimal('tunjangan_jabatan', 15, 2)->comment('Copy dari karyawan.tunjangan_jabatan');
            $table->decimal('tunjangan_transport', 15, 2)->comment('Copy dari karyawan.tunjangan_transport');
            $table->decimal('tunjangan_makan', 15, 2)->comment('Copy dari karyawan.tunjangan_makan');
            $table->decimal('tunjangan_lembur', 15, 2)->default(0.00)->comment('Dihitung dari tabel lembur');
            $table->decimal('bonus_kehadiran', 15, 2)->default(0.00)->comment('Dari aturan perusahaan');
            $table->decimal('bonus_kpi', 15, 2)->default(0.00)->comment('Dari tabel kpi');
            
            // Komponen potongan (dihitung dari absensi)
            $table->decimal('potongan_terlambat', 15, 2)->default(0.00)->comment('Sum dari absensi');
            $table->decimal('potongan_alpha', 15, 2)->default(0.00)->comment('Sum dari absensi');
            $table->decimal('potongan_lainnya', 15, 2)->default(0.00);
            $table->text('keterangan_potongan')->nullable();
            
            // Potongan wajib
            $table->decimal('bpjs_kesehatan', 15, 2)->default(0.00);
            $table->decimal('bpjs_ketenagakerjaan', 15, 2)->default(0.00);
            $table->decimal('pph21', 15, 2)->default(0.00);
            
            // Total
            $table->decimal('total_pendapatan', 15, 2);
            $table->decimal('total_potongan', 15, 2);
            $table->decimal('gaji_bersih', 15, 2);
            
            // Statistik kehadiran (summary dari absensi)
            $table->integer('jumlah_hadir')->default(0);
            $table->integer('jumlah_izin')->default(0);
            $table->integer('jumlah_sakit')->default(0);
            $table->integer('jumlah_alpha')->default(0);
            $table->integer('jumlah_terlambat')->default(0);
            $table->decimal('total_jam_lembur', 5, 2)->default(0.00);
            
            // Status dan metadata
            $table->enum('status', ['DRAFT', 'FINAL', 'DIBAYAR'])->default('DRAFT');
            $table->dateTime('tanggal_dibuat')->useCurrent();
            $table->dateTime('tanggal_dibayar')->nullable();
            $table->string('dibuat_oleh', 255)->nullable();
            $table->text('catatan_admin')->nullable();
            
            $table->timestamps();
            
            $table->unique(['karyawan_id', 'bulan', 'tahun'], 'unique_karyawan_periode');
            $table->index('karyawan_id', 'idx_karyawan_id');
            $table->index(['bulan', 'tahun'], 'idx_periode');
            $table->index('status', 'idx_status');
            
            $table->foreign('karyawan_id', 'fk_gaji_karyawan')
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
        Schema::dropIfExists('gaji');
    }
};
