<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengajuanMaintenance extends Model
{
  protected $table = 'permohonan_maintenance';
  protected $fillable = [
    'perangkat_id',
    'user_id',
    'lokasi_id',
    'keterangan',
    'nama_perangkat',
    'tipe',
  ];

  public function perangkats(): BelongsTo
  {
    return $this->belongsTo(Perangkat::class, 'perangkat_id');
  }
  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }

  public function lokasi(): BelongsTo
  {
    return $this->belongsTo(Lokasi::class, 'lokasi_id');
  }

  public function pemohon()
  {
    return $this->belongsTo(\App\Models\User::class, 'user_id');
  }
}
