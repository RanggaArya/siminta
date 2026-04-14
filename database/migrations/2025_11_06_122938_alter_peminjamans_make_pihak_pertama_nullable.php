<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('peminjamans', function (Blueprint $table) {
            $table->string('pihak_pertama_nama')->nullable()->change();
            $table->string('pihak_pertama_jabatan')->nullable()->change();
            $table->string('pihak_pertama_unit')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('peminjamans', function (Blueprint $table) {
            $table->string('pihak_pertama_nama')->nullable(false)->change();
            $table->string('pihak_pertama_jabatan')->nullable(false)->change();
            $table->string('pihak_pertama_unit')->nullable(false)->change();
        });
    }
};
