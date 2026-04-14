<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('riwayat_maintenances', function (Blueprint $t) {
            if (!Schema::hasColumn('riwayat_maintenances', 'user_id')) {
                $t->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete()->after('perangkat_id');
            }

            if (!Schema::hasColumn('riwayat_maintenances', 'tanggal_maintenance')) {
                $t->date('tanggal_maintenance')->nullable()->after('user_id');
                $t->index('tanggal_maintenance');
            }

            if (!Schema::hasColumn('riwayat_maintenances', 'lokasi_id')) {
                $t->foreignId('lokasi_id')->nullable()->constrained('lokasis')->nullOnDelete()->after('tanggal_maintenance');
            }

            if (!Schema::hasColumn('riwayat_maintenances', 'nama_pemilik')) {
                $t->string('nama_pemilik')->nullable()->after('lokasi_id');
            }

            if (!Schema::hasColumn('riwayat_maintenances', 'status_akhir')) {
                $t->enum('status_akhir', ['berfungsi', 'berfungsi_sebagian', 'tidak_berfungsi'])->nullable()->after('nama_pemilik');
                $t->index('status_akhir');
            }

            if (!Schema::hasColumn('riwayat_maintenances', 'catatan')) {
                $t->text('catatan')->nullable()->after('status_akhir');
            }

            if (!Schema::hasColumn('riwayat_maintenances', 'foto')) {
                $t->json('foto')->nullable()->after('catatan');
            }
        });
    }

    public function down(): void
    {
        Schema::table('riwayat_maintenances', function (Blueprint $t) {
            if (Schema::hasColumn('riwayat_maintenances', 'user_id')) {
                $t->dropConstrainedForeignId('user_id');
            }
            if (Schema::hasColumn('riwayat_maintenances', 'lokasi_id')) {
                $t->dropConstrainedForeignId('lokasi_id');
            }

            if (Schema::hasColumn('riwayat_maintenances', 'tanggal_maintenance')) {
                try {
                    $t->dropIndex(['tanggal_maintenance']);
                } catch (\Throwable $e) {
                }
                $t->dropColumn('tanggal_maintenance');
            }
            if (Schema::hasColumn('riwayat_maintenances', 'status_akhir')) {
                try {
                    $t->dropIndex(['status_akhir']);
                } catch (\Throwable $e) {
                }
                $t->dropColumn('status_akhir');
            }

            foreach (['nama_pemilik', 'catatan', 'foto'] as $col) {
                if (Schema::hasColumn('riwayat_maintenances', $col)) {
                    $t->dropColumn($col);
                }
            }
        });
    }
};
