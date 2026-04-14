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
        Schema::create('supervisi', function (Blueprint $table) {
            $table->id();
            
            // Menggunakan foreignId agar lebih clean dan otomatis mendukung BIGINT UNSIGNED
            $table->foreignId('perangkat_id')
                ->constrained('perangkats')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->timestamp('tanggal');
            $table->text('keterangan')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supervisi');
    }
};