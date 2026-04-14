<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('UPDATE riwayat_maintenances SET harga = 0 WHERE harga < 0');

        Schema::table('riwayat_maintenances', function (Blueprint $t) {
            if (method_exists($t, 'unsignedDecimal')) {
                $t->unsignedDecimal('harga', 15, 2)->nullable()->change();
            } else {
                $t->decimal('harga', 15, 2)->nullable()->change();
            }
        });

        DB::statement('ALTER TABLE riwayat_maintenances MODIFY harga DECIMAL(15,2) UNSIGNED NULL');

        Schema::table('riwayat_maintenances', function (Blueprint $t) {
            $t->index('created_at');
            $t->index('harga');
        });

        try {
            DB::statement('ALTER TABLE riwayat_maintenances ADD CONSTRAINT chk_rm_harga_non_negative CHECK (harga IS NULL OR harga >= 0)');
        } catch (\Throwable $e) {
        }
    }

    public function down(): void
    {
        try {
            DB::statement('ALTER TABLE riwayat_maintenances DROP CONSTRAINT chk_rm_harga_non_negative');
        } catch (\Throwable $e) {}

        Schema::table('riwayat_maintenances', function (Blueprint $t) {
            $t->dropIndex(['created_at']);
            $t->dropIndex(['harga']);
            $t->decimal('harga', 15, 2)->nullable()->change();
        });
    }
};
