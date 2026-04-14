<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('maintenance_types')) {
            Schema::create('maintenance_types', function (Blueprint $t) {
                $t->id();
                $t->string('nama')->unique();
                $t->timestamps();
            });
        }

        if (!Schema::hasTable('maintenance_type_riwayat')) {
            Schema::create('maintenance_type_riwayat', function (Blueprint $t) {
                $t->id();
                $t->foreignId('riwayat_maintenance_id')->constrained('riwayat_maintenances')->cascadeOnDelete();
                $t->foreignId('maintenance_type_id')->constrained('maintenance_types')->cascadeOnDelete();
                // nama index custom biar <64 char
                $t->unique(['riwayat_maintenance_id','maintenance_type_id'], 'mtri_rm_mt_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_type_riwayat');
        Schema::dropIfExists('maintenance_types');
    }
};
