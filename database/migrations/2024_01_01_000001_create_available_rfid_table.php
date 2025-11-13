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
        Schema::create('available_rfid', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('card_number', 255)->unique();
            $table->string('card_type', 100)->nullable();
            $table->enum('status', ['AVAILABLE', 'ASSIGNED', 'DAMAGED', 'LOST', 'INACTIVE'])->default('AVAILABLE');
            $table->dateTime('assigned_at')->nullable();
            $table->text('notes')->nullable();
            $table->uuid('karyawan_id')->nullable();
            $table->timestamps();
            
            $table->index('card_number', 'idx_card_number');
            $table->index('status', 'idx_status');
            $table->index('karyawan_id', 'idx_karyawan_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('available_rfid');
    }
};
