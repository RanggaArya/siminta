<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('inventory_number_counters', function (Blueprint $t) {
            $t->id();
            $t->foreignId('jenis_id')->constrained('jenis_perangkats')->cascadeOnDelete();
            $t->foreignId('kategori_id')->constrained('kategoris')->cascadeOnDelete();
            $t->year('tahun');
            $t->unsignedSmallInteger('last_number')->default(0);
            $t->timestamps();

            $t->unique(['jenis_id','kategori_id','tahun']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_number_counters');
    }
};
