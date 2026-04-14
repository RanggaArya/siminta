<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('komponens')) {
            Schema::create('komponens', function (Blueprint $t) {
                $t->id();
                $t->string('nama')->unique();
                $t->timestamps();
            });
        }

        if (!Schema::hasTable('komponen_riwayat')) {
            Schema::create('komponen_riwayat', function (Blueprint $t) {
                $t->id();
                $t->foreignId('riwayat_maintenance_id')->constrained('riwayat_maintenances')->cascadeOnDelete();
                $t->foreignId('komponen_id')->constrained('komponens')->cascadeOnDelete();
                // opsional: aksi per komponen
                $t->enum('aksi', ['dicek','diganti'])->nullable();

                $t->unique(['riwayat_maintenance_id','komponen_id'], 'kri_rm_km_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('komponen_riwayat');
        Schema::dropIfExists('komponens');
    }
};
