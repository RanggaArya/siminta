<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('jenis_perangkats', function (Blueprint $t) {
            if (!Schema::hasColumn('jenis_perangkats', 'prefix')) {
                $t->char('prefix', 1)->default('B')->after('nama_jenis');
            }
            if (!Schema::hasColumn('jenis_perangkats', 'kode_jenis')) {
                $t->string('kode_jenis', 10)->nullable()->after('prefix'); 
            }
        });
    }

    public function down(): void
    {
        Schema::table('jenis_perangkats', function (Blueprint $t) {
            if (Schema::hasColumn('jenis_perangkats', 'kode_jenis')) $t->dropColumn('kode_jenis');
            if (Schema::hasColumn('jenis_perangkats', 'prefix')) $t->dropColumn('prefix');
        });
    }
};
