<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('perangkats', function (Blueprint $table) {
            $table->timestamp('expired_sent_at')->nullable()->after('updated_at');
        });
    }

    public function down(): void
    {
        Schema::table('perangkats', function (Blueprint $table) {
            $table->dropColumn('expired_sent_at');
        });
    }
};

