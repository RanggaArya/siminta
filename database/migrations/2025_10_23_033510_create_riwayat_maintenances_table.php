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
        Schema::create('riwayat_maintenances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('perangkat_id')->constrained('perangkats')->onDelete('cascade');
            $table->string('deskripsi')->nullable();
            $table->decimal('harga', 15, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riwayat_maintenances');
    }
};
