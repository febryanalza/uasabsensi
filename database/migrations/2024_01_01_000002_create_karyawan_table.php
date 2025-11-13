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
        Schema::create('karyawan', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nip', 100)->unique();
            $table->string('rfid_card_number', 255)->unique()->nullable();
            $table->string('nama', 255);
            $table->string('email', 255)->unique();
            $table->string('jabatan', 255);
            $table->string('departemen', 255);
            $table->string('telepon', 50)->nullable();
            $table->text('alamat')->nullable();
            $table->date('tanggal_masuk')->nullable();
            $table->enum('status', ['AKTIF', 'CUTI', 'RESIGN'])->default('AKTIF');
            
            // Komponen Gaji Tetap (Master Data)
            $table->decimal('gaji_pokok', 15, 2)->default(0);
            $table->decimal('tunjangan_jabatan', 15, 2)->default(0);
            $table->decimal('tunjangan_transport', 15, 2)->default(0);
            $table->decimal('tunjangan_makan', 15, 2)->default(0);
            
            $table->timestamps();
            
            $table->index('nip', 'idx_nip');
            $table->index('email', 'idx_email');
            $table->index('departemen', 'idx_departemen');
            $table->index('status', 'idx_status');
            $table->index('rfid_card_number', 'idx_rfid_card_number');
            
            $table->foreign('rfid_card_number', 'fk_karyawan_rfid')
                  ->references('card_number')
                  ->on('available_rfid')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('karyawan');
    }
};
