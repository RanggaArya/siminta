<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('kategoris', function (Blueprint $t) {
            $t->id();
            $t->string('nama_kategori')->unique();
            $t->char('kode_kategori', 3); 
            $t->timestamps();

            $t->unique(['kode_kategori']); 
        });

        // Tambah FK ke perangkats
        Schema::table('perangkats', function (Blueprint $t) {
            if (!Schema::hasColumn('perangkats', 'kategori_id')) {
                $t->foreignId('kategori_id')->nullable()
                    ->constrained('kategoris')
                    ->nullOnDelete()
                    ->after('jenis_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('perangkats', function (Blueprint $t) {
            if (Schema::hasColumn('perangkats', 'kategori_id')) {
                $t->dropConstrainedForeignId('kategori_id');
            }
        });
        Schema::dropIfExists('kategoris');
    }
};
