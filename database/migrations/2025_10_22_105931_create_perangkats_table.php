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
        Schema::create('perangkats', function (Blueprint $table) {
            $table->id();

            $table->string('nama_perangkat')->index();
            $table->string('nomor_inventaris')->unique()->nullable();
            $table->string('kode')->nullable()->index();
            $table->string('tipe')->nullable()->index();

            $table->text('spesifikasi')->nullable();
            $table->text('deskripsi')->nullable();

            $table->string('perolehan')->nullable();
            $table->year('tahun_pengadaan')->nullable();
            $table->decimal('harga', 15, 2)->nullable();
            $table->date('tanggal_distribusi')->nullable();

            $table->foreignId('lokasi_id')->nullable()
                ->constrained('lokasis')
                ->onDelete('set null');

            $table->foreignId('jenis_id')->nullable()
                ->constrained('jenis_perangkats')
                ->onDelete('set null');

            $table->foreignId('status_id')->nullable()
                ->constrained('statuses')
                ->onDelete('set null');

            $table->foreignId('kondisi_id')->nullable()
                ->constrained('kondisis')
                ->onDelete('set null');

            $table->text('catatan')->nullable();
            $table->text('mutasi')->nullable();
            $table->text('upgrade')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('perangkats');
    }
};
