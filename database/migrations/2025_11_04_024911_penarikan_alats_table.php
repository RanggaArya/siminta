<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penarikan_alats', function (Blueprint $table) {
            $table->id();

            $table->foreignId('perangkat_id')->nullable()->constrained('perangkats')->onDelete('set null');

            $table->string('nama_perangkat')->nullable();
            $table->string('nomor_inventaris')->nullable();
            $table->string('tipe')->nullable();
            $table->text('spesifikasi')->nullable();
            $table->foreignId('lokasi_id')->nullable()->constrained('lokasis')->onDelete('set null'); 
            $table->integer('tahun_pembelian')->nullable(); 

            $table->date('tanggal_penarikan');
            $table->json('alasan_penarikan')->nullable(); 
            $table->text('alasan_lainnya')->nullable();

            $table->string('tindak_lanjut_tipe')->nullable();
            $table->text('tindak_lanjut_detail')->nullable(); 

            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penarikan_alats');
    }
};