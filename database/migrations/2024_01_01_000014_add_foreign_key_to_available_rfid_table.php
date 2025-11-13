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
        Schema::table('available_rfid', function (Blueprint $table) {
            $table->foreign('karyawan_id', 'fk_rfid_karyawan')
                  ->references('id')
                  ->on('karyawan')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('available_rfid', function (Blueprint $table) {
            $table->dropForeign('fk_rfid_karyawan');
        });
    }
};
