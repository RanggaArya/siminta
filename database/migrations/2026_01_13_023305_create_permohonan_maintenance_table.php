<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::create('permohonan_maintenance', function (Blueprint $table) {
      $table->id();
      $table->foreignId('perangkat_id')->constrained('perangkats')->onDelete('cascade');
      $table->string('nama_perangkat');
      $table->string('tipe')->nullable();
      $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
      $table->foreignId('lokasi_id')->nullable()->constrained('lokasis')->nullOnDelete();
      $table->string('keterangan');
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('permohonan_maintenance');
  }
};
