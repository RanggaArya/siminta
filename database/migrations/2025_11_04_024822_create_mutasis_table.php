<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mutasis', function (Blueprint $table) {
            $table->id();

            $table->foreignId('perangkat_id')->nullable()->constrained('perangkats')->onDelete('set null');

            $table->foreignId('lokasi_mutasi_id')->nullable()->constrained('lokasis')->onDelete('set null');

            $table->string('nama_perangkat')->nullable();
            $table->string('nomor_inventaris')->nullable();
            $table->string('tipe')->nullable();
            
            $table->foreignId('lokasi_asal_id')->nullable()->constrained('lokasis')->onDelete('set null');
            $table->foreignId('kondisi_id')->nullable()->constrained('kondisis')->onDelete('set null');

            $table->date('tanggal_mutasi')->nullable();
            $table->date('tanggal_diterima')->nullable();
            $table->text('alasan_mutasi')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mutasis');
    }
};