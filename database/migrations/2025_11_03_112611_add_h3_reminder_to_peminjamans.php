<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('peminjamans', function (Blueprint $table) {
            $table->timestamp('reminder_h3_sent_at')->nullable()->index();
            $table->string('peminjam_email')->nullable()->after('pihak_kedua_unit');
        });
    }

    public function down(): void
    {
        Schema::table('peminjamans', function (Blueprint $table) {
            $table->dropColumn(['reminder_h3_sent_at', 'peminjam_email']);
        });
    }
};
