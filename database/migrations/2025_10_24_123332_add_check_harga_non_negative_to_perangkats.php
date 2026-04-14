<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('UPDATE perangkats SET harga = 0 WHERE harga < 0');

        Schema::table('perangkats', function (Blueprint $t) {
            $t->decimal('harga', 15, 2)->unsigned()->nullable()->change();
        });

        DB::statement('ALTER TABLE perangkats ADD CONSTRAINT chk_perangkats_harga_non_negative CHECK (harga IS NULL OR harga >= 0)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE perangkats DROP CONSTRAINT chk_perangkats_harga_non_negative');

        Schema::table('perangkats', function (Blueprint $t) {
            $t->bigInteger('harga')->nullable()->change();
        });
    }
};
