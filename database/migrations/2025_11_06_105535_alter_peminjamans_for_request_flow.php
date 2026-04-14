<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('peminjamans', function (Blueprint $table) {
            $table->foreignId('requested_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->enum('status', ['Menunggu', 'Dipinjam', 'Dikembalikan', 'Terlambat', 'Ditolak'])
                  ->default('Menunggu')->change();

            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
        });
    }

    public function down(): void {
        Schema::table('peminjamans', function (Blueprint $table) {
            $table->dropConstrainedForeignId('requested_by_user_id');
            $table->dropConstrainedForeignId('approved_by_user_id');
            $table->dropColumn(['approved_at', 'rejected_at']);
            $table->enum('status', ['Dipinjam','Dikembalikan','Terlambat'])->default('Dipinjam')->change();
        });
    }
};
