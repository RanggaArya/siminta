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
        Schema::create('peminjamans', function (Blueprint $table) {
            $table->id();

            $table->string('pihak_pertama_nama');
            $table->string('pihak_pertama_jabatan')->nullable();
            $table->string('pihak_pertama_unit')->nullable();

            $table->string('pihak_kedua_nama');
            $table->string('pihak_kedua_jabatan')->nullable();
            $table->string('pihak_kedua_unit')->nullable();

            $table->foreignId('perangkat_id')->nullable()->constrained('perangkats')->nullOnDelete();
            $table->string('nomor_inventaris')->nullable()->index();
            $table->string('nama_barang');
            $table->string('merk')->nullable();
            $table->string('kondisi_terakhir')->nullable();

            $table->text('alasan_pinjam')->nullable();
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();

            $table->enum('status', ['Dipinjam', 'Dikembalikan', 'Terlambat'])->default('Dipinjam');
            $table->text('catatan')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peminjamans');
    }
};
